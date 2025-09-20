<?php

namespace App\Http\Admin;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Http\Controller;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Traits\WithTwigRenderTrait;

final class AmountLogsController extends Controller
{
    use WithTwigRenderTrait;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        AuthServiceInterface $authService,
        private readonly UserAccountLogRepositoryInterface $userAccountLogRepository,
    ) {
        parent::__construct($request, $response, $authService);
    }

    public function __invoke() : ResponseInterface
    {
        // если пользователь зашел куда его не просили
        if(!$this->authService->isAdmin()) {
            return $this->redirect('/');
        }

        $logs = $this->userAccountLogRepository->fetchAll();

        return $this->render('admin/amount_logs.html.twig', [
            'logs' => $logs,
        ]);
    }
}