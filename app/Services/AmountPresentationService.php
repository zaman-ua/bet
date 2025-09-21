<?php

namespace App\Services;

use App\Domain\MoneyFactory;

class AmountPresentationService
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
    ) {}


    public function fetchAmounts(array $amounts) : array
    {
        if(empty($amounts)) {
            return [];
        }

        foreach ($amounts as $key => $user) {
            if(!empty($user['amounts'])) {
                $amountsArray = explode(';', $user['amounts']);
                $amounts[$key]['amounts_array'] = $this->processAmounts($amountsArray);
            }
        }

        return $amounts;
    }


    public function fetchAmount(string $amount) : array
    {
        if(empty($amount)) {
            return [];
        }

        $amountsArray = explode(';', $amount);
        return $this->processAmounts($amountsArray);
    }



    private function processAmounts(array $amounts) : array
    {
        $result = [];

        if(!empty($amounts)) {
            foreach ($amounts as $amount) {
                [$amountRaw, $currencyCode] = explode(' ', $amount);
                $money = $this->moneyFactory->fromRaw($amountRaw, null, $currencyCode);

                $result[] = $money;
            }
        }

        return $result;
    }
}