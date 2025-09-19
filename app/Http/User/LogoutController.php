<?php

namespace App\Http\User;

use App\Core\Auth;
use App\Core\Http\ResponseInterface;
use App\Http\Controller;

class LogoutController extends Controller
{
    public function index() : ResponseInterface
    {
        Auth::clearRememberCookie();
        Auth::logoutUser();

        return $this->redirect('/users/login');
    }
}