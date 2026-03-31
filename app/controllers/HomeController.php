<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Models\MatchModel;
use App\Models\Message;
use App\Models\Interaction;
use App\Models\Profile;
use App\Models\Photo;
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

        // Recent matches (last 10 with photos) for the horizontal scroll
        $recentMatches = MatchModel::getByUserId($userId);
        $recentMatches = array_slice($recentMatches, 0, 10);

        // Photos for gallery strip
        $photos = Photo::getByUserId($userId);

        // Profile completeness
        $photoCount = count($photos);
        $completeness = $this->calcCompleteness($profile, $photoCount);

        $isPremium = !empty($profile['is_premium']);

        // Greeting based on time of day
        $hour = (int)date('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        View::render('home/dashboard', [
            'email'          => Session::get('user_email'),
            'profile'        => $profile,
            'matchCount'     => $matchCount,
            'unread'         => $unread,
            'swipesToday'    => $swipesToday,
            'dailyLimit'     => $dailyLimit,
            'hasProfile'     => $hasProfile,
            'recentMatches'  => $recentMatches,
            'photos'         => $photos,
            'photoCount'     => $photoCount,
            'completeness'   => $completeness,
            'isPremium'      => $isPremium,
            'greeting'       => $greeting,
        ]);
    }

    private function calcCompleteness(?array $profile, int $photoCount): int
    {
        if (!$profile) return 0;
        $score = 0;
        if (!empty($profile['name']))           $score += 15;
        if (!empty($profile['bio']))            $score += 15;
        if (!empty($profile['date_of_birth']))  $score += 10;
        if (!empty($profile['gender']))         $score += 10;
        if (!empty($profile['looking_for']))    $score += 5;
        if (!empty($profile['city']))           $score += 10;
        if ($photoCount >= 1)                   $score += 20;
        if ($photoCount >= 3)                   $score += 10;
        if (!empty($profile['relationship_goal']) && $profile['relationship_goal'] !== 'undecided') $score += 5;
        return min(100, $score);
    }
}