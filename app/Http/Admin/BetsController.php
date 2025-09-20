<?php

namespace App\Http\Admin;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Enums\BetStatusEnum;
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
        AuthServiceInterface $authService,
    ) {
        parent::__construct($request, $response, $authService);
    }

    public function index() : ResponseInterface
    {
        // если пользователь зашел куда его не просили
        if(!$this->authService->isAdmin()) {
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
        $data = $this->request->getPost();

        // валидация
        // TODO


        if(empty($data['bet_id']) || !BetStatusEnum::isValid($data['result'])) {
            throw new RuntimeException('wrong result');
        }

        $betPlayEnum = BetStatusEnum::from($data['result']);

        $betId = $this->betPlayService->play($data['bet_id'], $betPlayEnum);
        $bet = $this->betRepository->getById($betId);

        return $this->json([
            'ok' => true,
            'statusHtml' => $bet['status'],
            'payoutHtml' => $bet['payout']
        ]);
    }
}