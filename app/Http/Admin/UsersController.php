<?php

namespace App\Http\Admin;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Domain\MoneyFactory;
use App\Http\Controller;
use App\Interface\CurrencyRepositoryInterface;
use App\Interface\UserReaderRepositoryInterface;
use App\Services\AmountService;
use App\Traits\WithTwigTrait;

final class UsersController extends Controller
{
    use WithTwigTrait;

    public function __construct(
        RequestInterface                               $request,
        ResponseInterface                              $response,
        private readonly AmountService                 $amountService,
        private readonly CurrencyRepositoryInterface   $currencyRepository,
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        private readonly MoneyFactory                  $moneyFactory,
        AuthServiceInterface                           $authService,
    ) {
        parent::__construct($request, $response, $authService);
    }

    public function index() : ResponseInterface
    {
        // если пользователь зашел куда его не просили
        if(!$this->authService->isAdmin()) {
            return $this->redirect('/');
        }

        $users = $this->userReaderRepository->fetchAll();
        $currencies = $this->currencyRepository->getAssoc();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'currencies' => $currencies
        ]);
    }

    public function adjust() : ResponseInterface
    {
        $data = $this->request->getPost();
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

                $money = $this->moneyFactory->fromHuman($amount, $currencyId);

                $this->amountService->adjust(
                    userId: $data['user_id'],
                    currencyId: $currencyId,
                    amount: $money->amount,
                    comment: 'изменено администратором'
                );
            }
        }

        $amountArray = $this->userReaderRepository->fetchAmountsById($data['user_id']);

        $amountsHtml = $this->fetch('shared/user_amounts.html.twig', [
            'amounts_array' => $amountArray
        ]);

        return $this->json([
            'ok' => true,
            'amountsHtml' => $amountsHtml
        ]);
    }
}