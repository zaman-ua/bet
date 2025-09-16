<?php

namespace App\Http\Admin;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Domain\Money;
use App\Http\Controller;
use App\Repository\CurrencyRepository;
use App\Repository\UserAccountLogRepository;
use App\Repository\UserRepository;
use App\Services\AmountService;

final class AmountLogsController extends Controller
{
    public function __invoke() : Response
    {
        // если пользователь зашел куда его не просили
        if(!Auth::isAdmin()) {
            return $this->redirect('/');
        }

        $logs = (new UserAccountLogRepository())->fetchAll();

        return $this->render('admin/amount_logs.html.twig', [
            'logs' => $logs,
        ]);
    }
}