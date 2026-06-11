<?php 
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to dashboard if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

include('includes/header.php'); 
?>

<div class="auth-container">
    
    <div class="auth-card">
        <div class="row no-gutters">
            <!-- Left Side - Branding -->
            <div class="col-md-5">
                <div class="auth-left">
                    <img src="../images/logo.png" alt="ErrandsCall Logo" class="brand-logo">
                    <h2 class="auth-title">Welcome to ErrandsCall</h2>
                    <p class="auth-subtitle">Your trusted partner for all your vehicle service management needs.</p>
                    
                    <div class="auth-features mt-4">
                        <div class="auth-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Quick & Reliable Service</span>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Vehicle Management</span>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Service Tracking</span>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>24/7 Customer Support</span>
                        </div>
						  <div class="auth-feature auth-feature--full">
							  <a class="nav-link btn btn-gradient" href="../">Back to Main Site</a>
						  </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Authentication Forms -->
            <div class="col-md-7">
                <div class="auth-right">
                    <!-- Tabs Navigation -->
                    <div class="auth-tabs" data-active-tab="login">
                        <div class="auth-tab active" data-tab="login">Login</div>
                        <div class="auth-tab" data-tab="register">Register</div>
                        <div class="auth-tab" data-tab="forgot">Forgot Password</div>
                    </div>
                    
                    <!-- Login Form -->
                    <form id="loginForm" class="auth-form active" data-form="login" novalidate>
                        <div class="form-group">
                            <label for="loginUsername">Email / ID Number *</label>
                            <input type="text" class="form-control" id="loginUsername" name="username" required>
                            <div class="invalid-feedback">Please enter your email or ID number.</div>
                        </div>

                        <div class="form-group password-field">
                            <label for="loginPassword">Password *</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                            <button type="button" class="password-toggle" data-target="loginPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback">Password is required.</div>
                        </div>

                        <button type="submit" class="btn btn-gradient btn-block">
                            <span class="btn-text">Login</span>
                        </button>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <a href="#" class="text-gradient" data-tab="forgot">Forgot Password?</a>
                            <a href="#" class="text-gradient" data-tab="register">Create Account</a>
                        </div>
                    </form>
                    
                    <!-- Register Form -->
                    <form id="registerForm" class="auth-form" data-form="register" novalidate enctype="multipart/form-data">
                        <h5 class="text-gradient mb-3">Personal Details</h5>

                        <div class="form-group">
                            <label>Are You A Foreign National? *</label>
                            <div class="toggle-pill-group">
                                <div class="toggle-pill">
                                    <input type="radio" name="is_foreign_national" id="foreignNo" value="0" checked>
                                    <label for="foreignNo">No</label>
                                </div>
                                <div class="toggle-pill">
                                    <input type="radio" name="is_foreign_national" id="foreignYes" value="1">
                                    <label for="foreignYes">Yes</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label id="idNumberLabel" for="idNumber">ID Number *</label>
                                <input type="text" class="form-control" id="idNumber" name="id_number" required maxlength="13">
                                <small class="form-text text-muted" id="idNumberHint">13 digit South African ID number.</small>
                                <div class="invalid-feedback" id="idNumberFeedback">Please enter a valid 13-digit South African ID number.</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Date of Birth *</label>
                                <input type="date" class="form-control" id="dob" name="dob" required>
                                <div class="invalid-feedback">Please select your date of birth.</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Initials *</label>
                                <input type="text" class="form-control" name="initials" required maxlength="5">
                                <div class="invalid-feedback">Please enter your initials.</div>
                            </div>
                            <div class="form-group col-md-6 file-upload-field">
                                <label>ID Document (copy) *</label>
                                <input type="file" class="form-control" name="id_document" accept=".pdf,.jpg,.jpeg,.png" required>
                                <div class="file-name-hint">PDF, JPG or PNG, max 5MB.</div>
                                <div class="invalid-feedback">Please upload a copy of your ID document.</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Full Names *</label>
                                <input type="text" class="form-control" name="firstnames" required>
                                <div class="invalid-feedback">Please enter your full names.</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Surname *</label>
                                <input type="text" class="form-control" name="surname" required>
                                <div class="invalid-feedback">Please enter your surname.</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Email *</label>
                                <input type="email" class="form-control" id="registerEmail" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Confirm Email *</label>
                                <input type="email" class="form-control" id="registerConfirmEmail" name="confirm_email" required>
                                <div class="invalid-feedback">Email addresses must match.</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Cellphone Number *</label>
                            <input type="tel" class="form-control" name="phone" required pattern="[0-9+\-\s()]{10,}">
                            <small class="form-text text-muted">We may send an OTP to this number to verify it.</small>
                            <div class="invalid-feedback">Please enter a valid phone number.</div>
                        </div>

                        <div class="form-group">
                            <label>Physical Address Line 1 *</label>
                            <input type="text" class="form-control" name="address_line1" required>
                            <div class="invalid-feedback">Please enter your address.</div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Physical Address Line 2</label>
                                <input type="text" class="form-control" name="address_line2">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Physical Address Line 3</label>
                                <input type="text" class="form-control" name="address_line3">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Physical Address Line 4</label>
                                <input type="text" class="form-control" name="address_line4">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Postal Code *</label>
                                <input type="text" class="form-control" name="postal_code" required maxlength="10">
                                <div class="invalid-feedback">Please enter your postal code.</div>
                            </div>
                        </div>

                        <h5 class="text-gradient mb-3">Login Details</h5>
                        <div class="form-row">
                            <div class="form-group col-md-6 password-field">
                                <label>Password *</label>
                                <input type="password" class="form-control" id="registerPassword" name="password" required minlength="6">
                                <button type="button" class="password-toggle" data-target="registerPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                                <div class="password-strength mt-2">
                                    <div class="password-strength-bar"></div>
                                </div>
                            </div>
                            <div class="form-group col-md-6 password-field">
                                <label>Confirm Password *</label>
                                <input type="password" class="form-control" id="registerConfirmPassword" name="confirm_password" required>
                                <button type="button" class="password-toggle" data-target="registerConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Passwords must match.</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gradient btn-block">
                            <span class="btn-text">Register</span>
                        </button>
                        
                        <p class="text-center mt-3">Already a user? <a href="#" class="text-gradient" data-tab="login">Login</a></p>
                    </form>
                    
                    <!-- Forgot Password Form (2-step: request OTP, then enter OTP + new password) -->
                    <div class="auth-form" data-form="forgot">
                        <form id="forgotStep1Form" class="forgot-step active" novalidate>
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" class="form-control" name="email" required>
                                <div class="invalid-feedback">Valid email is required.</div>
                            </div>

                            <button type="submit" class="btn btn-gradient btn-block">
                                <span class="btn-text">Send OTP</span>
                            </button>

                            <p class="text-center mt-3">Remember your password? <a href="#" class="text-gradient" data-tab="login">Login</a></p>
                        </form>

                        <form id="forgotStep2Form" class="forgot-step" novalidate>
                            <input type="hidden" name="email" id="otpEmail">

                            <div class="form-group">
                                <label>OTP Code *</label>
                                <input type="text" class="form-control" name="otp" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}">
                                <div class="invalid-feedback">Please enter the 6-digit code sent to your email.</div>
                            </div>

                            <div class="form-group password-field">
                                <label>New Password *</label>
                                <input type="password" class="form-control" id="otpNewPassword" name="password" required minlength="6">
                                <button type="button" class="password-toggle" data-target="otpNewPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Password must be at least 6 characters.</div>
                                <div class="password-strength mt-2">
                                    <div class="password-strength-bar"></div>
                                </div>
                            </div>

                            <div class="form-group password-field">
                                <label>Confirm New Password *</label>
                                <input type="password" class="form-control" id="otpConfirmPassword" name="confirm_password" required>
                                <button type="button" class="password-toggle" data-target="otpConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Passwords must match.</div>
                            </div>

                            <button type="submit" class="btn btn-gradient btn-block">
                                <span class="btn-text">Reset Password</span>
                            </button>

                            <p class="text-center mt-3"><a href="#" class="text-gradient" data-tab="login">Back to Login</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>