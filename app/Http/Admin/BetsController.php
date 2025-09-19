<?php

namespace App\Http\Admin;

use App\Core\Auth;
use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Http\Controller;
use App\Interface\BetRepositoryInterface;
use App\Services\BetPlayService;
use App\Traits\WithTwigTrait;
use RuntimeException;

final class BetsController extends Controller
{
    use WithTwigTrait;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        private readonly BetPlayService $betPlayService,
        private readonly BetRepositoryInterface $betRepository,
    ) {
        parent::__construct($request, $response);
    }

    public function index() : ResponseInterface
    {
        // если пользователь зашел куда его не просили
        if(!Auth::isAdmin()) {
            return $this->redirect('/');
        }

        $bets = $this->betRepository->fetchAll();
        $matches = require APP_ROOT . '/config/matches.php';

        $bets = $this->betRepository->processMatches($bets, $matches);

        return $this->render('admin/bets.html.twig', [
            'bets' => $bets,
        ]);
    }

    public function play() : ResponseInterface
    {
        $data = $this->request->post;

        // валидация
        // TODO


        if(empty($data['bet_id']) || !in_array($data['result'], ['won','lost'])) {
            throw new RuntimeException('wrong result');
        }

        $betId = $this->betPlayService->play($data['bet_id'], $data['result']);
        $bet = $this->betRepository->getById($betId);

        return $this->json([
            'ok' => true,
            'statusHtml' => $bet['status'],
            'payoutHtml' => $bet['payout']
        ]);
    }
}