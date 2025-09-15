<?php

namespace App\Http\Admin;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Http\Controller;
use App\Repository\CurrencyRepository;
use App\Repository\UserRepository;
use App\Services\AmountService;

final class UsersController extends Controller
{
    public function __invoke() : Response
    {
        // если пользователь зашел куда его не просили
        if(!Auth::isAdmin()) {
            return $this->redirect('/');
        }

        $users = (new UserRepository())->fetchAll();
        $currencies = (new CurrencyRepository())->getAssoc();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'currencies' => $currencies
        ]);
    }

    public function adjust() : Response
    {
        $data = $this->request->post;
        $amountsHtml = '';

        if(empty($data['currency']) || empty($data['user_id'])) {
            return $this->json([
                'error'=>'currencies is empty'
            ], 400);
        }

        foreach ($data['currency'] as $currencyId => $amount) {
            if(!empty($data['user_id']) && !empty($currencyId) && $amount != 0) {

                // валидируем наличие пользователя, валюты, сумму
                // TODO


                (new AmountService())->adjust(
                    $data['user_id'],
                    $currencyId,
                    $amount,
                    'изменено администратором'
                );
            }

            $amountsHtml = (new UserRepository())->fetchAmountsById($data['user_id']);
        }

        return $this->json([
            'ok' => true,
            'amountsHtml' => $amountsHtml
        ]);
    }
}