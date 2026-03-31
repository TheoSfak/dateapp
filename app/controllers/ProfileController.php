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

        View::render('profile/edit', [
            'profile' => $profile,
            'photos'  => $photos,
        ]);
    }

    public function update(): void
    {
        $user = $this->requireAuth();
        CSRF::validate();

        Profile::update($user['id'], [
            'name'              => trim($_POST['name'] ?? ''),
            'bio'               => trim($_POST['bio'] ?? ''),
            'date_of_birth'     => $_POST['date_of_birth'] ?? '',
            'gender'            => $_POST['gender'] ?? '',
            'looking_for'       => $_POST['looking_for'] ?? '',
            'relationship_goal' => $_POST['relationship_goal'] ?? '',
            'height_cm'         => $_POST['height_cm'] ?? '',
            'smoking'           => $_POST['smoking'] ?? '',
            'drinking'          => $_POST['drinking'] ?? '',
            'city'              => trim($_POST['city'] ?? ''),
            'country'           => trim($_POST['country'] ?? ''),
            'latitude'          => $_POST['latitude'] ?? '',
            'longitude'         => $_POST['longitude'] ?? '',
        ]);

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
        $isMatched = \App\Models\Match::areMatched($user['id'], $targetId);

        View::render('profile/view', [
            'profile'   => $profile,
            'photos'    => $photos,
            'age'       => $age,
            'isMatched' => $isMatched,
        ]);
    }
}
