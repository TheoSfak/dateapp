<?php
    $gestureKey   = htmlspecialchars($gesture['key'] ?? '', ENT_QUOTES, 'UTF-8');
    $gestureLabel = htmlspecialchars($gesture['label'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<section class="verify-page">
    <div class="verify-header">
        <div class="verify-header-icon">🛡️</div>
        <h1>Identity Verification</h1>
        <p>Verify your identity to earn a trusted badge on your profile.</p>
    </div>

    <?php if ($isVerified): ?>
        <!-- Already Verified -->
        <div class="verify-status verify-status--approved">
            <div class="verify-status-icon">✅</div>
            <h2>You're Verified!</h2>
            <p>Your identity has been confirmed. Your verified badge is visible on your profile.</p>
            <a href="/dateapp/profile" class="btn btn-primary">View My Profile</a>
        </div>

    <?php elseif ($pending): ?>
        <!-- Pending Review -->
        <div class="verify-status verify-status--pending">
            <div class="verify-status-icon">⏳</div>
            <h2>Under Review</h2>
            <p>Your verification photo is being reviewed. This usually takes a few hours.</p>
            <div class="verify-gesture-reminder">
                <strong>Your gesture:</strong> <?= htmlspecialchars(\App\Models\Verification::getGestureLabel($pending['gesture']), ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

    <?php elseif ($latest && $latest['status'] === 'rejected'): ?>
        <!-- Rejected — try again -->
        <div class="verify-status verify-status--rejected">
            <div class="verify-status-icon">❌</div>
            <h2>Verification Not Approved</h2>
            <p>Your previous attempt wasn't approved. Please try again with a clearer photo.</p>
        </div>

        <?php // Show capture form below ?>
        <?php include __DIR__ . '/_capture_form.php'; ?>

    <?php else: ?>
        <!-- First time -->
        <div class="verify-explainer">
            <div class="verify-steps">
                <div class="verify-step">
                    <span class="verify-step-num">1</span>
                    <div>
                        <strong>Get your gesture</strong>
                        <p>We'll show you a specific pose to replicate.</p>
                    </div>
                </div>
                <div class="verify-step">
                    <span class="verify-step-num">2</span>
                    <div>
                        <strong>Take a selfie</strong>
                        <p>Use your webcam or upload a photo showing the gesture.</p>
                    </div>
                </div>
                <div class="verify-step">
                    <span class="verify-step-num">3</span>
                    <div>
                        <strong>Get verified</strong>
                        <p>Our team reviews it and you earn your badge!</p>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/_capture_form.php'; ?>
    <?php endif; ?>
</section>
