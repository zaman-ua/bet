<?php

namespace App\Http\Admin;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Enums\BetStatusEnum;
use App\Facade\BetMatchFacade;
use App\Http\Controller;
use App\Services\BetPlayService;
use App\Traits\WithTwigRenderTrait;
use RuntimeException;

final class BetsController extends Controller
{
    use WithTwigRenderTrait;

    public function __construct(
        RequestInterface                              $request,
        ResponseInterface                             $response,
        private readonly BetPlayService               $betPlayService,
        AuthServiceInterface                          $authService,
        private readonly BetMatchFacade               $betMatchFacade
    ) {
        parent::__construct($request, $response, $authService);
    }

    public function index() : ResponseInterface
    {
        // если пользователь зашел куда его не просили
        if(!$this->authService->isAdmin()) {
            return $this->redirect('/');
        }

        $bets = $this->betMatchFacade->getAllBetsWithMatches();

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
        $bet = $this->betMatchFacade->getBetById($betId);

        return $this->json([
            'ok' => true,
            'statusHtml' => $bet->status,
            'payoutHtml' => $bet->payout->toHuman()
        ]);
    }
}