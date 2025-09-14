<?php

namespace App\Http\User;

use App\Core\Auth;
use App\Core\Http\Response;
use App\Http\Controller;

class LogoutController extends Controller
{
    public function index() : Response
    {
        Auth::clearRememberCookie();
        Auth::logoutUser();

        return $this->redirect('/users/login');
    }
}