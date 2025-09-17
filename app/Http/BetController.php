<?php

namespace App\Http;

use App\Core\Auth;
use App\Core\Http\Response;
use App\DTO\BetCreateDTO;
use App\Enums\OutcomeEnum;
use App\Repository\BetRepository;
use App\Repository\UserRepository;
use App\Services\BettingService;
use App\Validation\CreateBetValidator;

final class BetController extends Controller
{
    public function store(): Response
    {
        if(!Auth::isLoggedIn()) {
            return $this->json([
                'error' => 'not logged in',
            ], 403);
        }

        try {
            $config = require APP_ROOT . '/config/bets.php';

            $data = $this->request->post;

            $userId         = Auth::getUserId();
            $currencyId     = (int)($data['currency_id'] ?? '');
            $matchId        = (string)($data['match_id'] ?? '');
            $outcome        = (string)($data['outcome'] ?? '');
            $coefficient    = CreateBetValidator::coefficientValidate((string)($data['coefficient'] ?? ''), $config);
            $stake          = CreateBetValidator::stakeValidate((string)($data['stake'] ?? ''), $currencyId, $config);

            // валидируем значение для enum
            CreateBetValidator::outcome($outcome);
            $outcomeEnumVal = OutcomeEnum::from($outcome); // получится ли такой фокус? если да то полезная штука

            $betId = (new BettingService())->place(new BetCreateDTO(
                userId: $userId,
                currencyId: $currencyId,
                matchId: $matchId,
                outcome: $outcomeEnumVal,
                stake: $stake,
                coefficient: $coefficient
            ));

            // обновляем баланс пользователя так же как и в админке
            $amountArray = (new UserRepository())->fetchAmountsById(Auth::getUserId());
            $amountsHtml = $this->fetch('shared/user_amounts.html.twig', [
                'amounts_array' => $amountArray
            ]);

            $bets = (new BetRepository())->fetchBetsByUserId($userId);
            $matches = require APP_ROOT . '/config/matches.php';
            $bets = (new BetRepository())->processMatches($bets ?? [], $matches);

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