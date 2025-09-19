<?php

namespace App\Http\Admin;

use App\Core\Http\RequestInterface;
use App\Core\Http\ResponseInterface;
use App\Core\Service\AuthService;
use App\Http\Controller;
use App\Interface\UserAccountLogRepositoryInterface;
use App\Traits\WithTwigTrait;

final class AmountLogsController extends Controller
{
    use WithTwigTrait;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        private readonly UserAccountLogRepositoryInterface $userAccountLogRepository,
        AuthService $authService,
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