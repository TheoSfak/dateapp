<section class="auth-section">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="text-muted">Sign in to continue your journey.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/dateapp/login" class="auth-form">
            <?= \App\Core\CSRF::field() ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus
                       value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="Your password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <p class="auth-footer">Don't have an account? <a href="/dateapp/register">Sign up free</a></p>
    </div>
</section>
