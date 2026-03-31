<?php
namespace App\Core;

/**
 * Simple view renderer.
 * Views are plain PHP files inside app/views/.
 */
class View
{
    /**
     * Render a view file with optional data.
     * $view uses dot or slash notation relative to app/views/
     */
    public static function render(string $view, array $data = []): void
    {
        // Allow both "auth/login" and "auth.login"
        $view = str_replace('.', '/', $view);
        $file = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($file)) {
            http_response_code(500);
            echo "View [{$view}] not found.";
            return;
        }

        // Extract data into local scope
        extract($data, EXTR_SKIP);

        // Capture view output
        ob_start();
        require $file;
        $content = ob_get_clean();

        // Wrap in layout
        $layoutFile = __DIR__ . '/../views/layouts/main.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render a view without the layout wrapper (for partials / AJAX).
     */
    public static function partial(string $view, array $data = []): void
    {
        $view = str_replace('.', '/', $view);
        $file = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($file)) {
            extract($data, EXTR_SKIP);
            require $file;
        }
    }
}
