<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Models\Verification;

class VerificationController extends Controller
{
    /**
     * Show the verification page (webcam capture).
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $isVerified = Verification::isVerified($user['id']);
        $pending = Verification::hasPending($user['id']);
        $latest = Verification::getLatestForUser($user['id']);

        // Assign a random gesture for new attempts
        $gesture = Verification::getRandomGesture();

        View::render('verification/index', [
            'isVerified' => $isVerified,
            'pending'    => $pending,
            'latest'     => $latest,
            'gesture'    => $gesture,
        ]);
    }

    /**
     * Submit verification photo (POST with file upload).
     */
    public function submit(): void
    {
        $user = $this->requireAuth();

        // CSRF check
        $token = $_POST['csrf_token'] ?? '';
        $stored = Session::get('_csrf_token', '');
        if (!hash_equals($stored, $token)) {
            Session::flash('error', 'Security token expired. Please try again.');
            $this->redirect('/verify-identity');
            return;
        }

        // Already verified?
        if (Verification::isVerified($user['id'])) {
            Session::flash('error', 'You are already verified.');
            $this->redirect('/verify-identity');
            return;
        }

        // Already pending?
        if (Verification::hasPending($user['id'])) {
            Session::flash('error', 'You already have a pending verification request.');
            $this->redirect('/verify-identity');
            return;
        }

        $gesture = trim($_POST['gesture'] ?? '');
        if (!$gesture) {
            Session::flash('error', 'Invalid gesture. Please try again.');
            $this->redirect('/verify-identity');
            return;
        }

        // Handle file upload
        if (!isset($_FILES['verification_photo']) || $_FILES['verification_photo']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Photo upload failed. Please try again.');
            $this->redirect('/verify-identity');
            return;
        }

        $file = $_FILES['verification_photo'];

        // Validate MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            Session::flash('error', 'Invalid file type. Only JPEG, PNG, and WebP allowed.');
            $this->redirect('/verify-identity');
            return;
        }

        // Validate size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            Session::flash('error', 'Photo too large. Maximum 5MB.');
            $this->redirect('/verify-identity');
            return;
        }

        // Save file
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $filename = 'verify_' . bin2hex(random_bytes(16)) . '.' . $ext;
        $destDir = __DIR__ . '/../../public/uploads/verifications';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $destPath = $destDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::flash('error', 'Failed to save photo. Please try again.');
            $this->redirect('/verify-identity');
            return;
        }

        $relativePath = 'uploads/verifications/' . $filename;
        Verification::create($user['id'], $gesture, $relativePath);

        Session::flash('success', 'Verification photo submitted! Our team will review it shortly.');
        $this->redirect('/verify-identity');
    }
}
