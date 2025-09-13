<?php

// все что с деньгами и коэффициентами храним в int дабы не попадать на ошибки округления
return [
    'min_bet'           => (int) (env('BET_MIN', 1) * 100),
    'max_bet'           => (int) (env('BET_MAX', 500) * 100),
    'min_coefficient'   => (int) (env('BET_MIN_COEFF', 1.01) * 100),
    'max_coefficient'   => (int) (env('BET_MAX_COEFF', 40.0) * 100),
];