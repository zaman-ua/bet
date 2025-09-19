<?php

namespace App\Http\User;

use App\Core\Http\ResponseInterface;
use App\Http\Controller;

class LogoutController extends Controller
{
    public function index() : ResponseInterface
    {
        $this->authService->logout();

        return $this->redirect('/users/login');
    }
}