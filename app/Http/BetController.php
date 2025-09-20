<?php

namespace App\Http;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Domain\MoneyFactory;
use App\DTO\BetCreateDTO;
use App\Enums\OutcomeEnum;
use App\FragmentsService\UserAmountFragmentsService;
use App\Interface\MatchConfigProviderInterface;
use App\Services\BettingService;
use App\Validation\CreateBetValidator;

final class BetController extends Controller
{
    public function __construct(
        RequestInterface                               $request,
        ResponseInterface                              $response,
        AuthServiceInterface                           $authService,
        private readonly BettingService                $bettingService,
        private readonly MoneyFactory                  $moneyFactory,
        private readonly MatchConfigProviderInterface  $matchConfigProvider,
        private readonly UserAmountFragmentsService    $userAmountFragmentsService,
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
            $config = $this->matchConfigProvider->getBetConfig();
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

            return $this->json([
                'ok' => true,
                'bet_id' => $betId,
                'status' => 'Ставка успешно создана',
                'amountsHtml' => $this->userAmountFragmentsService->buildAmountForUser($userId),
                'betsTable' => $this->userAmountFragmentsService->buildBetTableForUser($userId),
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