<?php

namespace App\Http;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Core\Interface\AuthServiceInterface;
use App\Domain\MoneyFactory;
use App\DTO\BetCreateDTO;
use App\Enums\OutcomeEnum;
use App\Interface\BetRepositoryInterface;
use App\Interface\UserRepositoryInterface;
use App\Services\BettingService;
use App\Traits\WithTwigTrait;
use App\Validation\CreateBetValidator;

final class BetController extends Controller
{
    use WithTwigTrait;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        private readonly BettingService $bettingService,
        private readonly BetRepositoryInterface $betRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly MoneyFactory $moneyFactory,
        AuthServiceInterface $authService,
    ) {
        parent::__construct($request, $response, $authService);
    }

    public function store(): ResponseInterface
    {
        if(!$this->authService->isLoggedIn()) {
            return $this->json([
                'error' => 'not logged in',
            ], 403);
        }

        try {
            $config = require APP_ROOT . '/config/bets.php';

            $data = $this->request->getPost();

            $userId         = $this->authService->getUserId();
            $currencyId     = (int)($data['currency_id'] ?? '');
            $matchId        = (string)($data['match_id'] ?? '');
            $outcome        = (string)($data['outcome'] ?? '');
            $coefficient    = CreateBetValidator::coefficientValidate((string)($data['coefficient'] ?? ''), $config);
            $stake          = CreateBetValidator::stakeValidate((string)($data['stake'] ?? ''), $currencyId, $config, $this->moneyFactory);

            // валидируем значение для enum
            CreateBetValidator::outcome($outcome);
            $outcomeEnumVal = OutcomeEnum::from($outcome); // получится ли такой фокус? если да то полезная штука

            $betId = $this->bettingService->place(new BetCreateDTO(
                userId: $userId,
                currencyId: $currencyId,
                matchId: $matchId,
                outcome: $outcomeEnumVal,
                stake: $stake,
                coefficient: $coefficient
            ));

            // обновляем баланс пользователя так же как и в админке
            $amountArray = $this->userRepository->fetchAmountsById($this->authService->getUserId());
            $amountsHtml = $this->fetch('shared/user_amounts.html.twig', [
                'amounts_array' => $amountArray
            ]);

            $bets = $this->betRepository->fetchBetsByUserId($userId);
            $matches = require APP_ROOT . '/config/matches.php';
            $bets = $this->betRepository->processMatches($bets ?? [], $matches);

            $betsTable = $this->fetch('shared/user_bets.html.twig', [
                'bets' => $bets
            ]);

            return $this->json([
                'ok' => true,
                'bet_id' => $betId,
                'status' => 'Ставка успешно создана',
                'amountsHtml' => $amountsHtml,
                'betsTable' => $betsTable
            ], 201); // код 201 "Создано"

        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 422); // ошибка

        } catch (\RuntimeException $e) {
            $code = $e->getMessage() === 'balance_not_found' ? 409 : 400; // 409 = не хватает баланса, 400 - не корректный запрос
            return $this->json([
                'error'=>$e->getMessage()
            ], $code);

        } catch (\Throwable $e) {
            return $this->json([
                'error'=>'server_error',
            ], 500); // когда уже совсем все плохо
        }
    }
}