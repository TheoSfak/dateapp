<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Models\MatchModel;
use App\Models\Message;
use App\Models\Interaction;
use App\Models\Profile;
use App\Core\Config;

class HomeController extends Controller
{
    public function index(): void
    {
        if (!Session::get('user_id')) {
            View::render('home/landing');
            return;
        }

        $userId = Session::get('user_id');
        $profile = Profile::getFullProfile($userId);
        $matchCount = MatchModel::countByUserId($userId);
        $unread = Message::totalUnread($userId);
        $swipesToday = Interaction::getTodaySwipeCount($userId);
        $dailyLimit = Config::get('app.free_daily_swipes', 50);
        $hasProfile = !empty($profile['name']);

        View::render('home/dashboard', [
            'email'       => Session::get('user_email'),
            'profile'     => $profile,
            'matchCount'  => $matchCount,
            'unread'      => $unread,
            'swipesToday' => $swipesToday,
            'dailyLimit'  => $dailyLimit,
            'hasProfile'  => $hasProfile,
        ]);
    }
}
