<!-- Verification capture form (included in index.php) -->
<div class="verify-capture">
    <div class="verify-gesture-card">
        <h3>Your Gesture</h3>
        <div class="verify-gesture-display"><?= $gestureLabel ?></div>
        <p class="verify-gesture-note">Replicate this gesture clearly in your photo.</p>
    </div>

    <div class="verify-camera-area">
        <video id="verifyVideo" class="verify-video" autoplay playsinline></video>
        <canvas id="verifyCanvas" class="verify-canvas" style="display:none"></canvas>
        <img id="verifyPreview" class="verify-preview" style="display:none" alt="Preview">

        <div class="verify-camera-controls">
            <button type="button" class="btn btn-primary" id="startCameraBtn">📷 Open Camera</button>
            <button type="button" class="btn btn-accent" id="captureBtn" style="display:none">📸 Take Photo</button>
            <button type="button" class="btn btn-outline btn-sm" id="retakeBtn" style="display:none">↩ Retake</button>
        </div>

        <div class="verify-or-divider">
            <span>or upload a photo</span>
        </div>
    </div>

    <form method="POST" action="/dateapp/verify-identity/submit" enctype="multipart/form-data" id="verifyForm">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\CSRF::token() ?>">
        <input type="hidden" name="gesture" value="<?= $gestureKey ?>">
        <input type="file" name="verification_photo" id="verifyFileInput" accept="image/jpeg,image/png,image/webp" class="form-input" style="display:none">

        <!-- Hidden file input populated by webcam capture  -->
        <input type="file" name="verification_photo" id="verifyHiddenFile" style="display:none">

        <label for="verifyFileInput" class="btn btn-outline btn-block verify-upload-btn">📁 Upload Photo from Device</label>

        <button type="submit" class="btn btn-primary btn-block verify-submit-btn" id="verifySubmitBtn" disabled>
            Submit for Verification
        </button>
    </form>
</div>

<script>
(function() {
    const video = document.getElementById('verifyVideo');
    const canvas = document.getElementById('verifyCanvas');
    const preview = document.getElementById('verifyPreview');
    const startBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const fileInput = document.getElementById('verifyFileInput');
    const form = document.getElementById('verifyForm');
    const submitBtn = document.getElementById('verifySubmitBtn');
    let stream = null;
    let capturedBlob = null;

    startBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 640, height: 480 } });
            video.srcObject = stream;
            video.style.display = 'block';
            startBtn.style.display = 'none';
            captureBtn.style.display = '';
        } catch (e) {
            alert('Could not access camera. Please upload a photo instead.');
        }
    });

    captureBtn.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(blob => {
            capturedBlob = blob;
            preview.src = URL.createObjectURL(blob);
            preview.style.display = 'block';
            video.style.display = 'none';
            captureBtn.style.display = 'none';
            retakeBtn.style.display = '';
            submitBtn.disabled = false;
            if (stream) stream.getTracks().forEach(t => t.stop());
        }, 'image/jpeg', 0.9);
    });

    retakeBtn.addEventListener('click', async () => {
        capturedBlob = null;
        preview.style.display = 'none';
        retakeBtn.style.display = 'none';
        submitBtn.disabled = true;
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 640, height: 480 } });
            video.srcObject = stream;
            video.style.display = 'block';
            captureBtn.style.display = '';
        } catch (e) {
            startBtn.style.display = '';
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            capturedBlob = null;
            preview.src = URL.createObjectURL(fileInput.files[0]);
            preview.style.display = 'block';
            video.style.display = 'none';
            captureBtn.style.display = 'none';
            startBtn.style.display = 'none';
            retakeBtn.style.display = '';
            submitBtn.disabled = false;
            if (stream) stream.getTracks().forEach(t => t.stop());
        }
    });

    form.addEventListener('submit', (e) => {
        if (capturedBlob) {
            e.preventDefault();
            const fd = new FormData(form);
            // Remove empty file inputs
            fd.delete('verification_photo');
            fd.append('verification_photo', capturedBlob, 'verification.jpg');
            fetch(form.action, { method: 'POST', body: fd })
                .then(r => { window.location.href = '/dateapp/verify-identity'; })
                .catch(() => { alert('Upload failed. Please try again.'); });
        }
        // If not capturedBlob, normal file upload proceeds
    });
})();
</script>
