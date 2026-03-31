<?php
namespace App\Core;

/**
 * CSRF token generation and validation.
 */
class CSRF
{
    /**
     * Generate a new token (or return the existing one for this session).
     */
    public static function token(): string
    {
        if (!Session::get('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    /**
     * Return a hidden input element containing the token.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    /**
     * Validate the submitted token against the session token.
     */
    public static function validate(): bool
    {
        $submitted = $_POST['_csrf_token'] ?? '';
        $stored    = Session::get('_csrf_token', '');
        if (!hash_equals($stored, $submitted)) {
            http_response_code(403);
            exit('Invalid CSRF token.');
        }
        return true;
    }
}
