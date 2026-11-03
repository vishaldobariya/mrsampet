<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>

<div class="modal fade" id="verifyOtpModal" tabindex="-1" role="dialog" aria-labelledby="verifyOtpModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title flex-item" style="color:#000 !important;" id="headerPetInfo">Verify Otp</h5>
        <button type="button" class="close  flex-item" data-dismiss="modal" aria-label="Close" style="color:#000 !important;" onclick="closeModal('verifyOtpModal')">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-danger" id="errorMessage"></p>
        <label for="otp-input">Enter OTP:</label>
        <input type="text" id="otp-input" name="otp">
      </div>
      <div class="modal-footer">
        <button type="button" class="forminator-button btn-warning" id="resend-otp-button">Resend OTP</button>
        <button type="button" class="forminator-button btn-success" id="verify-otp-button">Verify OTP</button>
      </div>
    </div>
  </div>
</div>

