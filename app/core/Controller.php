<?php
namespace App\Core;

/**
 * Base Controller – shared helpers for all controllers
 */
abstract class Controller
{
    protected function redirect(string $path): void
    {
        $base = rtrim(Config::get('app.url'), '/');
        header("Location: {$base}{$path}");
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Require the user to be authenticated; redirect to login otherwise.
     */
    protected function requireAuth(): array
    {
        if (!Session::get('user_id')) {
            $this->redirect('/login');
        }
        return [
            'id'    => Session::get('user_id'),
            'email' => Session::get('user_email'),
        ];
    }

    /**
     * Require the user to be a guest (not logged in).
     */
    protected function requireGuest(): void
    {
        if (Session::get('user_id')) {
            $this->redirect('/');
        }
    }
}
