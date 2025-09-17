<?php

namespace App\Http\Admin;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Http\Controller;
use App\Repository\BetRepository;
use App\Services\BetPlayService;
use RuntimeException;

final class BetsController extends Controller
{
    public function __invoke() : Response
    {
        // если пользователь зашел куда его не просили
        if(!Auth::isAdmin()) {
            return $this->redirect('/');
        }

        $bets = (new BetRepository())->fetchAll();
        $matches = require APP_ROOT . '/config/matches.php';

        if(!empty($bets) && !empty($matches)) {
            foreach ($bets as $key => $bet) {
                if(isset($matches[$bet['match_id']])) {
                    $bets[$key]['match'] = $matches[$bet['match_id']]['win'] . ' - ' . $matches[$bet['match_id']]['loss'];
                }
            }
        }

        return $this->render('admin/bets.html.twig', [
            'bets' => $bets,
        ]);
    }

    public function play() : Response
    {
        $data = $this->request->post;

        // валидация
        // TODO


        if(empty($data['bet_id']) || !in_array($data['result'], ['won','lost'])) {
            throw new RuntimeException('wrong result');
        }

        $betId = (new BetPlayService())->play($data['bet_id'], $data['result']);
        $bet = (new BetRepository())->getById($betId);

        return $this->json([
            'ok' => true,
            'statusHtml' => $bet['status'],
            'payoutHtml' => $bet['payout']
        ]);
    }
}