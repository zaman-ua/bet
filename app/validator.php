<?php

use App\Validation\RequestValidator;
use App\Validation\Rules\BooleanRule;
use App\Validation\Rules\DateRule;
use App\Validation\Rules\EmailRule;
use App\Validation\Rules\InRule;
use App\Validation\Rules\IntRule;
use App\Validation\Rules\MaxRule;
use App\Validation\Rules\MinRule;
use App\Validation\Rules\NullableRule;
use App\Validation\Rules\RequiredRule;
use App\Validation\Rules\StringRule;

RequestValidator::registerRuleHandlers(
    new NullableRule(),
    new RequiredRule(),
    new StringRule(),
    new EmailRule(),
    new IntRule(),
    new BooleanRule(),
    new MinRule(),
    new MaxRule(),
    new InRule(),
    new DateRule(),
);