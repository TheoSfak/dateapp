<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Core\Session;
use App\Core\CSRF;
use App\Models\User;

class AuthController extends Controller
{
    // ─── REGISTER ────────────────────────────────────────────
    public function showRegister(): void
    {
        $this->requireGuest();
        View::render('auth/register');
    }

    public function register(): void
    {
        $this->requireGuest();
        CSRF::validate();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $errors   = [];

        // Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8 || strlen($password) > 128) {
            $errors[] = 'Password must be 8–128 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
        if (User::emailExists($email)) {
            $errors[] = 'This email is already registered.';
        }

        if (!empty($errors)) {
            View::render('auth/register', [
                'errors' => $errors,
                'email'  => $email,
            ]);
            return;
        }

        $userId = User::create($email, $password);

        // Auto-login after registration
        Session::set('user_id', $userId);
        Session::set('user_email', $email);
        Session::flash('success', 'Welcome to DateApp! Complete your profile to start matching.');
        $this->redirect('/');
    }

    // ─── LOGIN ───────────────────────────────────────────────
    public function showLogin(): void
    {
        $this->requireGuest();
        View::render('auth/login');
    }

    public function login(): void
    {
        $this->requireGuest();
        CSRF::validate();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::authenticate($email, $password);

        if (!$user) {
            View::render('auth/login', [
                'errors' => ['Invalid email or password.'],
                'email'  => $email,
            ]);
            return;
        }

        if ($user['status'] !== 'active') {
            View::render('auth/login', [
                'errors' => ['Your account has been suspended.'],
                'email'  => $email,
            ]);
            return;
        }

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::flash('success', 'Welcome back!');
        $this->redirect('/');
    }

    // ─── LOGOUT ──────────────────────────────────────────────
    public function logout(): void
    {
        CSRF::validate();
        Session::destroy();
        // Start a new session for the flash
        Session::start();
        Session::flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }

    // ─── EMAIL VERIFICATION ─────────────────────────────────
    public function verify(): void
    {
        $token = $_GET['token'] ?? '';
        if ($token !== '' && User::verifyEmail($token)) {
            Session::flash('success', 'Email verified successfully!');
        } else {
            Session::flash('error', 'Invalid or expired verification link.');
        }
        $this->redirect('/login');
    }
}
