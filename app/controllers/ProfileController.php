<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\CSRF;
use App\Models\Profile;
use App\Models\Photo;

class ProfileController extends Controller
{
    public function show(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::getFullProfile($user['id']);
        $photos  = Photo::getByUserId($user['id']);
        $age     = Profile::calculateAge($profile['date_of_birth'] ?? null);

        View::render('profile/show', [
            'profile' => $profile,
            'photos'  => $photos,
            'age'     => $age,
        ]);
    }

    public function edit(): void
    {
        $user = $this->requireAuth();
        $profile = Profile::getByUserId($user['id']);
        $photos  = Photo::getByUserId($user['id']);

        // Interest tags
        $allInterests = Profile::getAllInterests();
        $userInterestIds = Profile::getUserInterestIds($user['id']);

        // Deal-breakers
        $dealbreakers = Profile::getDealbreakers($user['id']);
        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);

        View::render('profile/edit', [
            'profile'          => $profile,
            'photos'           => $photos,
            'allInterests'     => $allInterests,
            'userInterestIds'  => $userInterestIds,
            'dealbreakers'     => $dealbreakers,
            'isPremium'        => $isPremium,
        ]);
    }

    public function update(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();

        $errors = [];

        $name = trim($_POST['name'] ?? '');
        if ($name !== '' && (mb_strlen($name) < 2 || mb_strlen($name) > 100)) {
            $errors[] = 'Name must be 2-100 characters.';
        }

        $bio = trim($_POST['bio'] ?? '');
        if (mb_strlen($bio) > 2000) {
            $errors[] = 'Bio must be under 2000 characters.';
        }

        $dob = $_POST['date_of_birth'] ?? '';
        if ($dob !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            $errors[] = 'Invalid date of birth format.';
        } elseif ($dob !== '') {
            $age = Profile::calculateAge($dob);
            if ($age !== null && ($age < 18 || $age > 120)) {
                $errors[] = 'You must be between 18 and 120 years old.';
            }
        }

        $gender = $_POST['gender'] ?? '';
        if ($gender !== '' && !in_array($gender, ['male', 'female', 'non-binary', 'other'])) {
            $errors[] = 'Invalid gender.';
        }

        $lookingFor = $_POST['looking_for'] ?? '';
        if ($lookingFor !== '' && !in_array($lookingFor, ['male', 'female', 'everyone'])) {
            $errors[] = 'Invalid looking for value.';
        }

        $goal = $_POST['relationship_goal'] ?? '';
        if ($goal !== '' && !in_array($goal, ['long-term', 'short-term', 'friendship', 'casual', 'undecided'])) {
            $errors[] = 'Invalid relationship goal.';
        }

        $heightCm = $_POST['height_cm'] ?? '';
        if ($heightCm !== '' && (!is_numeric($heightCm) || $heightCm < 50 || $heightCm > 300)) {
            $errors[] = 'Height must be between 50 and 300 cm.';
        }

        $smoking = $_POST['smoking'] ?? '';
        if ($smoking !== '' && !in_array($smoking, ['never', 'sometimes', 'regularly'])) {
            $errors[] = 'Invalid smoking value.';
        }

        $drinking = $_POST['drinking'] ?? '';
        if ($drinking !== '' && !in_array($drinking, ['never', 'sometimes', 'regularly'])) {
            $errors[] = 'Invalid drinking value.';
        }

        $lat = $_POST['latitude'] ?? '';
        if ($lat !== '' && (!is_numeric($lat) || $lat < -90 || $lat > 90)) {
            $errors[] = 'Invalid latitude.';
        }

        $lng = $_POST['longitude'] ?? '';
        if ($lng !== '' && (!is_numeric($lng) || $lng < -180 || $lng > 180)) {
            $errors[] = 'Invalid longitude.';
        }

        if (!empty($errors)) {
            \App\Core\Session::flash('error', implode(' ', $errors));
            $this->redirect('/profile/edit');
            return;
        }

        Profile::update($user['id'], [
            'name'              => $name,
            'bio'               => $bio,
            'date_of_birth'     => $dob,
            'gender'            => $gender,
            'looking_for'       => $lookingFor,
            'relationship_goal' => $goal,
            'height_cm'         => $heightCm,
            'smoking'           => $smoking,
            'drinking'          => $drinking,
            'city'              => trim($_POST['city'] ?? ''),
            'country'           => trim($_POST['country'] ?? ''),
            'latitude'          => $lat,
            'longitude'         => $lng,
        ]);

        // Recalculate profile completeness
        Profile::recalcCompleteness($user['id']);

        \App\Core\Session::flash('success', 'Profile updated successfully!');
        $this->redirect('/profile');
    }

    public function uploadPhoto(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();

        if (!isset($_FILES['photo'])) {
            \App\Core\Session::flash('error', 'No file uploaded.');
            $this->redirect('/profile/edit');
            return;
        }

        $isPrimary = isset($_POST['is_primary']);
        $result = Photo::upload($user['id'], $_FILES['photo'], $isPrimary);

        if ($result) {
            \App\Core\Session::flash('success', 'Photo uploaded!');
        } else {
            \App\Core\Session::flash('error', 'Upload failed. Check file type and size (max 5MB, JPG/PNG/WebP).');
        }
        $this->redirect('/profile/edit');
    }

    public function setPrimaryPhoto(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();
        $photoId = (int)($_POST['photo_id'] ?? 0);
        Photo::setPrimary($photoId, $user['id']);
        \App\Core\Session::flash('success', 'Primary photo updated.');
        $this->redirect('/profile/edit');
    }

    public function deletePhoto(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();
        $photoId = (int)($_POST['photo_id'] ?? 0);
        Photo::delete($photoId, $user['id']);
        \App\Core\Session::flash('success', 'Photo deleted.');
        $this->redirect('/profile/edit');
    }

    /**
     * Save user interests (AJAX or form POST).
     */
    public function updateInterests(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();

        $ids = $_POST['interests'] ?? [];
        if (!is_array($ids)) $ids = [];

        // Validate: only allow valid integer IDs
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        Profile::saveInterests($user['id'], $ids);
        \App\Core\Session::flash('success', 'Interests updated!');
        $this->redirect('/profile/edit');
    }

    /**
     * Save user deal-breakers.
     */
    public function updateDealbreakers(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();

        $isPremium = (bool)(\App\Models\User::findById($user['id'])['is_premium'] ?? false);

        $dealbreakers = [];
        $smokingVal = $_POST['dealbreaker_smoking'] ?? '';
        $allowedSmoking = ['never', 'sometimes', 'regularly'];
        if (in_array($smokingVal, $allowedSmoking)) {
            $dealbreakers[] = ['field' => 'smoking', 'value' => $smokingVal];
        }

        Profile::saveDealbreakers($user['id'], $dealbreakers, $isPremium);
        \App\Core\Session::flash('success', 'Deal-breakers updated!');
        $this->redirect('/profile/edit');
    }

    /**
     * View another user's profile (from swipe or match).
     */
    public function viewUser(): void
    {
        $user = $this->requireAuth();
        $targetId = (int)($_GET['id'] ?? 0);
        if ($targetId <= 0) $this->redirect('/discover');

        $profile = Profile::getFullProfile($targetId);
        if (!$profile) $this->redirect('/discover');

        $photos = Photo::getByUserId($targetId);
        $age = Profile::calculateAge($profile['date_of_birth'] ?? null);
        $isMatched = \App\Models\MatchModel::areMatched($user['id'], $targetId);

        View::render('profile/view', [
            'profile'   => $profile,
            'photos'    => $photos,
            'age'       => $age,
            'isMatched' => $isMatched,
        ]);
    }
}
