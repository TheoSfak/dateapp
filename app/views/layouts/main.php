<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DateApp – Find Your Match</title>
    <link rel="stylesheet" href="/dateapp/public/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="/dateapp/" class="logo">💕 DateApp</a>
            <nav class="main-nav">
                <?php if (\App\Core\Session::get('user_id')): ?>
                    <span class="nav-user"><?= htmlspecialchars(\App\Core\Session::get('user_email'), ENT_QUOTES, 'UTF-8') ?></span>
                    <form method="POST" action="/dateapp/logout" class="inline-form">
                        <?= \App\Core\CSRF::field() ?>
                        <button type="submit" class="btn btn-sm btn-outline">Logout</button>
                    </form>
                <?php else: ?>
                    <a href="/dateapp/login" class="btn btn-sm btn-outline">Login</a>
                    <a href="/dateapp/register" class="btn btn-sm btn-primary">Sign Up</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php
        $success = \App\Core\Session::getFlash('success');
        $error   = \App\Core\Session::getFlash('error');
        if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> DateApp. All rights reserved.</p>
        </div>
    </footer>
    <script src="/dateapp/public/js/app.js"></script>
</body>
</html>
