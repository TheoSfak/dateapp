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

        // Update last_active_at (throttle to once per minute via session)
        $lastPing = Session::get('_last_active_ping', 0);
        if (time() - $lastPing > 60) {
            Database::getInstance()->query(
                "UPDATE users SET last_active_at = NOW() WHERE id = ?",
                [Session::get('user_id')]
            );
            Session::set('_last_active_ping', time());
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
