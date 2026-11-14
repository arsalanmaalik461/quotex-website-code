<?php $__env->startSection('title', 'Identity Verification (KYC) | Quotex'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.kyc-container {
  max-width: 860px;
  margin: 24px auto;
  padding: 0 16px;
}

.kyc-card {
  background: #1e222d;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 28px;
  margin-bottom: 24px;
  box-shadow: 0 12px 36px rgba(0,0,0,0.35);
}

.kyc-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.kyc-title {
  font-size: 20px;
  font-weight: 700;
  color: #fff;
  display: flex;
  align-items: center;
  gap: 10px;
}

.kyc-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}
.kyc-badge.verified {
  background: rgba(0, 200, 83, 0.15);
  color: #00e676;
  border: 1px solid rgba(0, 200, 83, 0.4);
}
.kyc-badge.pending {
  background: rgba(255, 171, 0, 0.15);
  color: #ffab00;
  border: 1px solid rgba(255, 171, 0, 0.4);
}
.kyc-badge.rejected {
  background: rgba(255, 82, 82, 0.15);
  color: #ff5252;
  border: 1px solid rgba(255, 82, 82, 0.4);
}
.kyc-badge.unverified {
  background: rgba(255, 255, 255, 0.1);
  color: #b0bec5;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.kyc-alert {
  padding: 16px 20px;
  border-radius: 12px;
  margin-bottom: 24px;
  font-size: 14px;
  line-height: 1.6;
}
.kyc-alert-success {
  background: rgba(0, 200, 83, 0.12);
  border: 1px solid rgba(0, 200, 83, 0.3);
  color: #b9f6ca;
}
.kyc-alert-warning {
  background: rgba(255, 171, 0, 0.12);
  border: 1px solid rgba(255, 171, 0, 0.3);
  color: #ffe57f;
}
.kyc-alert-danger {
  background: rgba(255, 82, 82, 0.12);
  border: 1px solid rgba(255, 82, 82, 0.4);
  color: #ff8a80;
}

.rejection-box {
  background: rgba(255, 82, 82, 0.08);
  border-left: 4px solid #ff5252;
  padding: 14px 18px;
  border-radius: 0 10px 10px 0;
  margin-top: 10px;
  font-size: 14px;
  color: #ffebee;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.field-label {
  font-size: 13px;
  font-weight: 600;
  color: #cfd8dc;
}
.field-input {
  background: #141720;
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #fff;
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}
.field-input:focus {
  border-color: #00c853;
}

/* Upload zone */
.upload-card {
  background: #141720;
  border: 2px dashed rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  position: relative;
  transition: all 0.2s;
  cursor: pointer;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.upload-card:hover {
  border-color: #00c853;
  background: rgba(0, 200, 83, 0.04);
}
.upload-card input[type="file"] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
}
.upload-icon {
  font-size: 32px;
  margin-bottom: 8px;
}
.upload-title {
  font-size: 14px;
  font-weight: 600;
  color: #eceff1;
}
.upload-subtitle {
  font-size: 12px;
  color: #90a4ae;
  margin-top: 4px;
}
.upload-preview {
  max-width: 100%;
  max-height: 160px;
  border-radius: 8px;
  object-fit: cover;
  display: none;
  margin-top: 8px;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

/* Front Camera Selfie Section */
.camera-section {
  background: #141720;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 14px;
  padding: 22px;
  margin-bottom: 24px;
  text-align: center;
}
.camera-header {
  margin-bottom: 16px;
}
.camera-header h3 {
  font-size: 16px;
  color: #fff;
  margin-bottom: 4px;
}
.camera-header p {
  font-size: 12px;
  color: #90a4ae;
}

.camera-viewport-wrap {
  position: relative;
  width: 100%;
  max-width: 380px;
  height: 380px;
  margin: 0 auto 16px;
  border-radius: 50%;
  overflow: hidden;
  background: #0b0e14;
  border: 3px solid #00c853;
  box-shadow: 0 0 24px rgba(0, 200, 83, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
}
@media (max-width: 480px) {
  .camera-viewport-wrap {
    max-width: 280px;
    height: 280px;
  }
}

#selfie-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scaleX(-1); /* Mirror for front camera */
  display: none;
}

.face-guide-oval {
  position: absolute;
  inset: 15%;
  border: 2px dashed rgba(255, 255, 255, 0.6);
  border-radius: 50%;
  pointer-events: none;
  display: none;
  animation: pulseGuide 2s infinite ease-in-out;
}
@keyframes pulseGuide {
  0% { border-color: rgba(255, 255, 255, 0.4); }
  50% { border-color: rgba(0, 200, 83, 0.9); }
  100% { border-color: rgba(255, 255, 255, 0.4); }
}

#selfie-preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: none;
  border-radius: 50%;
}

.camera-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #78909c;
  padding: 20px;
}
.camera-placeholder svg {
  width: 64px;
  height: 64px;
  stroke: #546e7a;
  margin-bottom: 12px;
}

.camera-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-cam {
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}
.btn-cam-primary {
  background: #00c853;
  color: #000;
}
.btn-cam-primary:hover {
  background: #00e676;
}
.btn-cam-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.2);
}
.btn-cam-secondary:hover {
  background: rgba(255, 255, 255, 0.16);
}

.btn-submit-kyc {
  width: 100%;
  padding: 14px;
  background: #00c853;
  color: #000;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 16px rgba(0, 200, 83, 0.3);
}
.btn-submit-kyc:hover {
  background: #00e676;
  transform: translateY(-1px);
}

.docs-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-top: 16px;
}
.doc-thumbnail-card {
  background: #141720;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 10px;
  text-align: center;
}
.doc-thumbnail-card img {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 6px;
  margin-bottom: 8px;
}
.doc-thumbnail-card .caption {
  font-size: 12px;
  font-weight: 600;
  color: #cfd8dc;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="kyc-container">

  
  <div style="margin-bottom: 16px; display:flex; justify-content:space-between; align-items:center;">
    <a href="<?php echo e(route('profile')); ?>" style="color: #90a4ae; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
      <span>←</span> Back to Account
    </a>
    <span style="font-size: 12px; color: #78909c;">User ID: #<?php echo e($user->id); ?></span>
  </div>

  <?php if(session('success')): ?>
    <div class="kyc-alert kyc-alert-success">
      ✓ <?php echo e(session('success')); ?>

    </div>
  <?php endif; ?>

  <?php if($errors->any()): ?>
    <div class="kyc-alert kyc-alert-danger">
      <strong>Please correct the following errors:</strong>
      <ul style="margin: 8px 0 0 16px;">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
    </div>
  <?php endif; ?>

  
  <?php if($kyc && $kyc->status === 'approved'): ?>
    
    <div class="kyc-card">
      <div class="kyc-header">
        <div class="kyc-title">
          <span>Identity Verification</span>
        </div>
        <div class="kyc-badge verified">
          <span>✓</span> Verified Account
        </div>
      </div>

      <div class="kyc-alert kyc-alert-success">
        <h4 style="margin: 0 0 6px 0; font-size: 16px; color: #00e676;">Your KYC is Approved!</h4>
        <p style="margin: 0;">Your identity has been fully verified by our compliance team on <?php echo e($kyc->reviewed_at ? $kyc->reviewed_at->format('M d, Y') : 'recently'); ?>. You have complete access to unlimited trading, fast deposits, and instant withdrawals.</p>
      </div>

      <div class="form-grid" style="margin-top: 20px;">
        <div class="field-group">
          <div class="field-label">Verified Full Name</div>
          <div class="field-input" style="background:#0f1218;"><?php echo e($kyc->full_name); ?></div>
        </div>
        <div class="field-group">
          <div class="field-label">Verified Phone Number</div>
          <div class="field-input" style="background:#0f1218;"><?php echo e($kyc->phone); ?></div>
        </div>
      </div>

      <h4 style="color:#fff; font-size: 14px; margin: 20px 0 10px 0;">Submitted & Verified Documents</h4>
      <div class="docs-preview-grid">
        <div class="doc-thumbnail-card">
          <img src="<?php echo e($kyc->id_card_front_url); ?>" alt="ID Card Front" onclick="window.open(this.src, '_blank')">
          <div class="caption">ID Card (Front)</div>
        </div>
        <div class="doc-thumbnail-card">
          <img src="<?php echo e($kyc->id_card_back_url); ?>" alt="ID Card Back" onclick="window.open(this.src, '_blank')">
          <div class="caption">ID Card (Back)</div>
        </div>
        <div class="doc-thumbnail-card">
          <img src="<?php echo e($kyc->live_selfie_url); ?>" alt="Live Selfie" onclick="window.open(this.src, '_blank')">
          <div class="caption">Live Front-Camera Selfie</div>
        </div>
      </div>
    </div>

  <?php elseif($kyc && $kyc->status === 'pending'): ?>
    
    <div class="kyc-card">
      <div class="kyc-header">
        <div class="kyc-title">
          <span>Identity Verification</span>
        </div>
        <div class="kyc-badge pending">
          <span>⏳</span> Pending Review
        </div>
      </div>

      <div class="kyc-alert kyc-alert-warning">
        <h4 style="margin: 0 0 6px 0; font-size: 16px; color: #ffab00;">Verification Under Review</h4>
        <p style="margin: 0;">We have received your KYC submission. Our security and compliance team is currently reviewing your ID documents and live selfie. Verification usually takes between 15 minutes to 2 hours.</p>
      </div>

      <div class="form-grid">
        <div class="field-group">
          <div class="field-label">Applicant Name</div>
          <div class="field-input" style="background:#0f1218;"><?php echo e($kyc->full_name); ?></div>
        </div>
        <div class="field-group">
          <div class="field-label">Phone Number</div>
          <div class="field-input" style="background:#0f1218;"><?php echo e($kyc->phone); ?></div>
        </div>
      </div>

      <h4 style="color:#fff; font-size: 14px; margin: 20px 0 10px 0;">Submitted Documents</h4>
      <div class="docs-preview-grid">
        <div class="doc-thumbnail-card">
          <img src="<?php echo e($kyc->id_card_front_url); ?>" alt="ID Card Front">
          <div class="caption">ID Card (Front)</div>
        </div>
        <div class="doc-thumbnail-card">
          <img src="<?php echo e($kyc->id_card_back_url); ?>" alt="ID Card Back">
          <div class="caption">ID Card (Back)</div>
        </div>
        <div class="doc-thumbnail-card">
          <img src="<?php echo e($kyc->live_selfie_url); ?>" alt="Live Selfie">
          <div class="caption">Live Front-Camera Selfie</div>
        </div>
      </div>
    </div>

  <?php else: ?>
    
    <?php if($kyc && $kyc->status === 'rejected'): ?>
      <div class="kyc-alert kyc-alert-danger">
        <div style="display:flex; align-items:center; gap:8px; font-weight:700; font-size:16px;">
          <span>❌</span> Verification Rejected
        </div>
        <p style="margin: 8px 0 4px 0;">Your previous KYC verification submission was rejected by our admin compliance team.</p>
        <div class="rejection-box">
          <strong>Reason for Rejection:</strong><br>
          <?php echo e($kyc->rejection_reason ?? 'Your documents did not meet our verification criteria. Please provide clear photos of your ID and live selfie.'); ?>

        </div>
        <p style="margin: 10px 0 0 0; font-size: 13px;">Please review the reason above and re-submit your corrected documents and selfie below.</p>
      </div>
    <?php endif; ?>

    <div class="kyc-card">
      <div class="kyc-header">
        <div>
          <div class="kyc-title">
            <span>Identity Verification (KYC)</span>
          </div>
          <p style="color: #90a4ae; font-size: 13px; margin: 4px 0 0 0;">
            Please enter your accurate legal information and upload valid government ID cards and a live front-camera selfie.
          </p>
        </div>
        <div class="kyc-badge <?php echo e($kyc && $kyc->status === 'rejected' ? 'rejected' : 'unverified'); ?>">
          <?php echo e($kyc && $kyc->status === 'rejected' ? 'Rejected — Please Resubmit' : 'Not Verified'); ?>

        </div>
      </div>

      <form id="kyc-form" action="<?php echo e(route('kyc.submit')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        
        <div class="form-grid">
          <div class="field-group">
            <label class="field-label" for="full_name">Full Legal Name *</label>
            <input type="text" id="full_name" name="full_name" class="field-input" placeholder="e.g. Muhammad Ali" value="<?php echo e(old('full_name', $kyc->full_name ?? $user->name)); ?>" required>
            <span style="font-size: 11px; color: #78909c;">Must match the name printed on your ID card</span>
          </div>

          <div class="field-group">
            <label class="field-label" for="phone">Active Phone Number *</label>
            <input type="tel" id="phone" name="phone" class="field-input" placeholder="e.g. +92 300 1234567" value="<?php echo e(old('phone', $kyc->phone ?? $user->phone)); ?>" required>
            <span style="font-size: 11px; color: #78909c;">Include country code</span>
          </div>
        </div>

        
        <h4 style="color:#fff; font-size:14px; margin: 24px 0 12px 0;">Government ID Card Documents</h4>
        <div class="form-grid">
          
          <div class="field-group">
            <div class="field-label">ID Card (Front Side) *</div>
            <div class="upload-card" onclick="document.getElementById('id_card_front').click()">
              <input type="file" id="id_card_front" name="id_card_front" accept="image/*" onchange="previewImage(this, 'preview-front')">
              <div class="upload-icon">🪪</div>
              <div class="upload-title">Upload ID Card Front</div>
              <div class="upload-subtitle">Click to choose from Gallery or Camera (JPG, PNG)</div>
              <img id="preview-front" class="upload-preview" alt="Front Preview">
            </div>
          </div>

          
          <div class="field-group">
            <div class="field-label">ID Card (Back Side) *</div>
            <div class="upload-card" onclick="document.getElementById('id_card_back').click()">
              <input type="file" id="id_card_back" name="id_card_back" accept="image/*" onchange="previewImage(this, 'preview-back')">
              <div class="upload-icon">🪪</div>
              <div class="upload-title">Upload ID Card Back</div>
              <div class="upload-subtitle">Click to choose from Gallery or Camera (JPG, PNG)</div>
              <img id="preview-back" class="upload-preview" alt="Back Preview">
            </div>
          </div>
        </div>

        
        <div class="camera-section">
          <div class="camera-header">
            <h3>📷 Live Front-Camera Selfie *</h3>
            <p>You must take a live selfie using your device's front camera to confirm your identity.</p>
          </div>

          
          <input type="hidden" name="live_selfie_data" id="live_selfie_data">
          
          <canvas id="selfie-canvas" style="display:none;"></canvas>

          <div class="camera-viewport-wrap" id="camera-wrap">
            
            <div class="camera-placeholder" id="cam-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                <circle cx="12" cy="13" r="4"></circle>
              </svg>
              <span style="font-size: 13px; font-weight: 600;">Front Camera Inactive</span>
              <span style="font-size: 11px; margin-top: 4px;">Click below to open front camera</span>
            </div>

            
            <video id="selfie-video" autoplay playsinline muted></video>
            <div class="face-guide-oval" id="face-guide"></div>

            
            <img id="selfie-preview-img" alt="Captured Selfie">
          </div>

          <div class="camera-actions">
            <button type="button" class="btn-cam btn-cam-primary" id="btn-start-camera" onclick="startFrontCamera()">
              <span>📸</span> Open Front Camera
            </button>
            <button type="button" class="btn-cam btn-cam-primary" id="btn-capture-selfie" style="display:none;" onclick="captureSelfie()">
              <span>⚡</span> Capture Live Selfie
            </button>
            <button type="button" class="btn-cam btn-cam-secondary" id="btn-retake-selfie" style="display:none;" onclick="retakeSelfie()">
              <span>🔄</span> Retake Selfie
            </button>
          </div>

          
          <div style="margin-top: 14px; font-size: 12px; color: #78909c;">
            <span>Or if camera does not start: </span>
            <label style="color: #00e676; text-decoration: underline; cursor: pointer;">
              Open Front Camera Directly
              <input type="file" name="live_selfie" id="live_selfie_file" accept="image/*" capture="user" style="display:none;" onchange="handleSelfieFile(this)">
            </label>
          </div>
          <div id="selfie-status-indicator" style="margin-top: 10px; font-size: 13px; font-weight: 600; display: none;"></div>
        </div>

        <button type="submit" class="btn-submit-kyc" id="btn-submit">
          Submit KYC Verification
        </button>
      </form>
    </div>
  <?php endif; ?>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
  // Image preview helper for ID front & back
  function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  // Live Front Camera logic
  let videoStream = null;
  const video = document.getElementById('selfie-video');
  const canvas = document.getElementById('selfie-canvas');
  const previewImg = document.getElementById('selfie-preview-img');
  const placeholder = document.getElementById('cam-placeholder');
  const faceGuide = document.getElementById('face-guide');
  const btnStart = document.getElementById('btn-start-camera');
  const btnCapture = document.getElementById('btn-capture-selfie');
  const btnRetake = document.getElementById('btn-retake-selfie');
  const dataInput = document.getElementById('live_selfie_data');
  const statusIndicator = document.getElementById('selfie-status-indicator');

  async function startFrontCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('Camera access is not supported by your browser. Please use the direct front camera link below.');
      document.getElementById('live_selfie_file').click();
      return;
    }

    try {
      // Prefer exact front camera, fallback to general user-facing camera
      const constraints = {
        video: {
          facingMode: 'user',
          width: { ideal: 720 },
          height: { ideal: 720 }
        },
        audio: false
      };

      videoStream = await navigator.mediaDevices.getUserMedia(constraints);
      video.srcObject = videoStream;
      video.style.display = 'block';
      placeholder.style.display = 'none';
      previewImg.style.display = 'none';
      faceGuide.style.display = 'block';

      btnStart.style.display = 'none';
      btnCapture.style.display = 'inline-flex';
      btnRetake.style.display = 'none';

      if (statusIndicator) {
        statusIndicator.style.display = 'block';
        statusIndicator.style.color = '#00e676';
        statusIndicator.innerText = '● Front Camera Active - Look at the camera';
      }
    } catch (err) {
      console.error('Camera error:', err);
      // Fallback try with basic video: true
      try {
        videoStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        video.srcObject = videoStream;
        video.style.display = 'block';
        placeholder.style.display = 'none';
        previewImg.style.display = 'none';
        faceGuide.style.display = 'block';
        btnStart.style.display = 'none';
        btnCapture.style.display = 'inline-flex';
        btnRetake.style.display = 'none';
      } catch (fallbackErr) {
        alert('Could not access front camera: ' + err.message + '. Please check camera permissions or use the direct camera capture option.');
        document.getElementById('live_selfie_file').click();
      }
    }
  }

  function captureSelfie() {
    if (!video || !video.videoWidth) {
      alert('Camera stream is not ready yet. Please wait a second and try again.');
      return;
    }

    const width = video.videoWidth;
    const height = video.videoHeight;
    canvas.width = width;
    canvas.height = height;

    const ctx = canvas.getContext('2d');
    // Mirror snapshot horizontally to match front-camera preview
    ctx.translate(width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0, width, height);

    const base64Image = canvas.toDataURL('image/jpeg', 0.9);
    dataInput.value = base64Image;

    // Show preview image
    previewImg.src = base64Image;
    previewImg.style.display = 'block';
    video.style.display = 'none';
    faceGuide.style.display = 'none';

    // Stop video tracks
    stopCameraStream();

    btnCapture.style.display = 'none';
    btnRetake.style.display = 'inline-flex';

    if (statusIndicator) {
      statusIndicator.style.display = 'block';
      statusIndicator.style.color = '#00e676';
      statusIndicator.innerText = '✓ Live Front-Camera Selfie Captured!';
    }
  }

  function retakeSelfie() {
    dataInput.value = '';
    previewImg.style.display = 'none';
    startFrontCamera();
  }

  function stopCameraStream() {
    if (videoStream) {
      videoStream.getTracks().forEach(track => track.stop());
      videoStream = null;
    }
  }

  function handleSelfieFile(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        previewImg.src = e.target.result;
        previewImg.style.display = 'block';
        placeholder.style.display = 'none';
        video.style.display = 'none';
        faceGuide.style.display = 'none';
        dataInput.value = e.target.result;

        btnStart.style.display = 'none';
        btnCapture.style.display = 'none';
        btnRetake.style.display = 'inline-flex';

        if (statusIndicator) {
          statusIndicator.style.display = 'block';
          statusIndicator.style.color = '#00e676';
          statusIndicator.innerText = '✓ Front-Camera Selfie Captured from Phone!';
        }
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  // Form submission validation
  const kycForm = document.getElementById('kyc-form');
  if (kycForm) {
    kycForm.addEventListener('submit', function(e) {
      const selfieData = dataInput.value;
      const selfieFile = document.getElementById('live_selfie_file');
      const hasSelfie = (selfieData && selfieData.length > 50) || (selfieFile && selfieFile.files.length > 0);

      if (!hasSelfie) {
        e.preventDefault();
        alert('Please take a live selfie using your front camera before submitting.');
        document.getElementById('camera-wrap').scrollIntoView({ behavior: 'smooth' });
        return false;
      }

      document.getElementById('btn-submit').innerText = 'Submitting KYC...';
      document.getElementById('btn-submit').disabled = true;
    });
  }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u881240793/domains/arslanmalik.tech/public_html/trade/resources/views/account/kyc.blade.php ENDPATH**/ ?>