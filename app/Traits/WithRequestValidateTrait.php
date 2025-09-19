<?php

namespace App\Traits;

use App\Validation\RequestValidator;

trait WithRequestValidateTrait
{
    public function validate(array $data, array $validation) : array
    {
        [$result, $errors] = RequestValidator::validate($data, $validation);

        $this->errors = $errors;
        return $result;
    }
}