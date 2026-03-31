<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;

class HomeController extends Controller
{
    public function index(): void
    {
        if (!Session::get('user_id')) {
            View::render('home/landing');
            return;
        }

        View::render('home/dashboard', [
            'email' => Session::get('user_email'),
        ]);
    }
}
