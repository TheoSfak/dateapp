<section class="auth-section">
    <div class="auth-card">
        <h2>Create Your Account</h2>
        <p class="text-muted">Join thousands of singles looking for love.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/dateapp/register" class="auth-form">
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
                       minlength="8" placeholder="At least 8 characters">
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required
                       minlength="8" placeholder="Re-enter your password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </form>

        <p class="auth-footer">Already have an account? <a href="/dateapp/login">Log in</a></p>
    </div>
</section>
