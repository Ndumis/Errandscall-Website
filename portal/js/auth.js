$(document).ready(function () {

    // ---------- Tab switching ----------
    $('.auth-tab').on('click', function () {
        switchTab($(this).data('tab'));
    });

    $('a[data-tab]').on('click', function (e) {
        e.preventDefault();
        switchTab($(this).data('tab'));
    });

    function switchTab(tabId) {
        $('.auth-tab').removeClass('active');
        $(`.auth-tab[data-tab="${tabId}"]`).addClass('active');

        $('.auth-form').removeClass('active');
        $(`.auth-form[data-form="${tabId}"]`).addClass('active');

        $('.auth-tabs').attr('data-active-tab', tabId);

        $('.alert').remove();
    }

    // ---------- Password show/hide toggle ----------
    $('.password-toggle').on('click', function () {
        const targetId = $(this).data('target');
        const $field = $(`#${targetId}`);
        const type = $field.attr('type') === 'password' ? 'text' : 'password';
        $field.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // ---------- Password strength meter ----------
    $('#registerPassword, #otpNewPassword').on('input', function () {
        const $container = $(this).closest('.password-field').find('.password-strength');
        const password = $(this).val();

        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/\d/)) strength++;
        if (password.match(/[^a-zA-Z\d]/)) strength++;

        $container.removeClass('weak medium strong');
        if (strength > 0) {
            $container.addClass(['weak', 'medium', 'strong'][strength - 1]);
        }
    });

    // ---------- Generic field-match validation (passwords / emails) ----------
    function validateMatch($field, $reference, message) {
        if ($field.val() !== $reference.val()) {
            $field[0].setCustomValidity(message);
        } else {
            $field[0].setCustomValidity('');
        }
    }

    $('#registerConfirmPassword').on('input', function () {
        validateMatch($(this), $('#registerPassword'), 'Passwords must match');
    });
    $('#registerPassword').on('input', function () {
        if ($('#registerConfirmPassword').val()) {
            validateMatch($('#registerConfirmPassword'), $(this), 'Passwords must match');
        }
    });

    $('#otpConfirmPassword').on('input', function () {
        validateMatch($(this), $('#otpNewPassword'), 'Passwords must match');
    });
    $('#otpNewPassword').on('input', function () {
        if ($('#otpConfirmPassword').val()) {
            validateMatch($('#otpConfirmPassword'), $(this), 'Passwords must match');
        }
    });

    $('#registerConfirmEmail').on('input', function () {
        validateMatch($(this), $('#registerEmail'), 'Email addresses must match');
    });
    $('#registerEmail').on('input', function () {
        if ($('#registerConfirmEmail').val()) {
            validateMatch($('#registerConfirmEmail'), $(this), 'Email addresses must match');
        }
    });

    // ---------- DOB age validation ----------
    function validateDob($input) {
        const dob = new Date($input.val());
        if (isNaN(dob.getTime())) return;

        const today = new Date();
        const minAge = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate());
        const maxAge = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());

        if (dob < minAge || dob > maxAge) {
            $input[0].setCustomValidity('Please enter a valid date of birth (13-100 years old)');
        } else {
            $input[0].setCustomValidity('');
        }
    }

    $('#dob').on('change', function () {
        validateDob($(this));
    });

    // ---------- Foreign national toggle / SA ID handling ----------
    const $idNumber = $('#idNumber');
    const $idLabel = $('#idNumberLabel');
    const $idHint = $('#idNumberHint');
    const $idFeedback = $('#idNumberFeedback');

    function isForeignNational() {
        return $('input[name="is_foreign_national"]:checked').val() === '1';
    }

    function isValidSAID(id) {
        if (!/^\d{13}$/.test(id)) return false;

        // Validate embedded date of birth (YYMMDD)
        const yy = parseInt(id.substr(0, 2), 10);
        const mm = parseInt(id.substr(2, 2), 10);
        const dd = parseInt(id.substr(4, 2), 10);
        const currentYY = new Date().getFullYear() % 100;
        const century = (yy <= currentYY) ? 2000 : 1900;
        const year = century + yy;
        const dob = new Date(year, mm - 1, dd);
        if (dob.getFullYear() !== year || dob.getMonth() !== mm - 1 || dob.getDate() !== dd) {
            return false;
        }

        // Luhn checksum validation
        let sumOdd = 0;
        for (let i = 0; i < 12; i += 2) {
            sumOdd += parseInt(id.charAt(i), 10);
        }

        let evenDigits = '';
        for (let i = 1; i < 12; i += 2) {
            evenDigits += id.charAt(i);
        }
        const evenDoubled = (parseInt(evenDigits, 10) * 2).toString();
        let sumEven = 0;
        for (let i = 0; i < evenDoubled.length; i++) {
            sumEven += parseInt(evenDoubled.charAt(i), 10);
        }

        const checkDigit = (10 - ((sumOdd + sumEven) % 10)) % 10;
        return checkDigit === parseInt(id.charAt(12), 10);
    }

    function validateIdNumber() {
        if (!$idNumber.length) return;
        if (isForeignNational()) {
            $idNumber[0].setCustomValidity('');
            return;
        }
        const value = $idNumber.val().trim();
        if (value === '' || isValidSAID(value)) {
            $idNumber[0].setCustomValidity('');
        } else {
            $idNumber[0].setCustomValidity('Please enter a valid 13-digit South African ID number.');
        }
    }

    function updateIdNumberField() {
        if (isForeignNational()) {
            $idLabel.text('Traffic Register Number *');
            $idHint.text('');
            $idFeedback.text('Please enter your Traffic Register Number.');
            $idNumber.attr('maxlength', 20);
        } else {
            $idLabel.text('ID Number *');
            $idHint.text('13 digit South African ID number.');
            $idFeedback.text('Please enter a valid 13-digit South African ID number.');
            $idNumber.attr('maxlength', 13);
        }
        validateIdNumber();
    }

    function tryAutofillDob() {
        if (!$idNumber.length) return;
        if (isForeignNational()) return;
        const idNumber = $idNumber.val().trim();
        if (!/^\d{13}$/.test(idNumber)) return;

        const yy = parseInt(idNumber.substr(0, 2), 10);
        const mm = parseInt(idNumber.substr(2, 2), 10);
        const dd = parseInt(idNumber.substr(4, 2), 10);
        const currentYY = new Date().getFullYear() % 100;
        const century = (yy <= currentYY) ? 2000 : 1900;
        const year = century + yy;

        const candidate = new Date(year, mm - 1, dd);
        if (candidate.getFullYear() === year && candidate.getMonth() === mm - 1 && candidate.getDate() === dd) {
            const iso = `${year}-${String(mm).padStart(2, '0')}-${String(dd).padStart(2, '0')}`;
            $('#dob').val(iso).trigger('change');
        }
    }

    $('input[name="is_foreign_national"]').on('change', function () {
        updateIdNumberField();
        tryAutofillDob();
    });

    $idNumber.on('input blur', function () {
        validateIdNumber();
        tryAutofillDob();
    });

    // Initialize ID number field state on load
    updateIdNumberField();

    // ---------- ID document file validation ----------
    $('input[name="id_document"]').on('change', function () {
        const file = this.files[0];
        if (!file) {
            this.setCustomValidity('');
            return;
        }

        const allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
        const ext = file.name.split('.').pop().toLowerCase();
        const maxSize = 5 * 1024 * 1024;

        if (!allowedExt.includes(ext)) {
            this.setCustomValidity('Please upload a PDF, JPG or PNG file.');
        } else if (file.size > maxSize) {
            this.setCustomValidity('File size must not exceed 5MB.');
        } else {
            this.setCustomValidity('');
        }
    });

    // ---------- Login form ----------
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        submitForm($(this), 'php/process-login.php');
    });

    // ---------- Register form (with file upload) ----------
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();

        validateMatch($('#registerConfirmPassword'), $('#registerPassword'), 'Passwords must match');
        validateMatch($('#registerConfirmEmail'), $('#registerEmail'), 'Email addresses must match');
        validateIdNumber();
        validateDob($('#dob'));

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $form.find('.alert').remove();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        const formData = new FormData(this);

        $.ajax({
            url: 'php/process-register.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert($form, response.message, 'success');
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                } else {
                    showAlert($form, response.message, 'danger');
                }
            },
            error: function () {
                showAlert($form, 'An error occurred. Please try again.', 'danger');
            },
            complete: function () {
                $submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // ---------- Forgot password: Step 1 - request OTP ----------
    $('#forgotStep1Form').on('submit', function (e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $form.find('.alert').remove();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        const email = $form.find('input[name="email"]').val();

        $.ajax({
            url: 'php/process-forgot.php',
            type: 'POST',
            data: { email: email },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#otpEmail').val(email);
                    $('#forgotStep2Form').find('.alert').remove();
                    $('#forgotStep1Form').removeClass('active');
                    $('#forgotStep2Form').addClass('active');

                    const alertType = response.otp ? 'warning' : 'success';
                    showAlert($('#forgotStep2Form'), response.message, alertType);
                } else {
                    showAlert($form, response.message, 'danger');
                }
            },
            error: function () {
                showAlert($form, 'An error occurred. Please try again.', 'danger');
            },
            complete: function () {
                $submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // ---------- Forgot password: Step 2 - verify OTP & reset password ----------
    $('#forgotStep2Form').on('submit', function (e) {
        e.preventDefault();

        validateMatch($('#otpConfirmPassword'), $('#otpNewPassword'), 'Passwords must match');

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $form.find('.alert').remove();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        $.ajax({
            url: 'php/process-reset.php',
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert($form, response.message, 'success');
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                } else {
                    showAlert($form, response.message, 'danger');
                }
            },
            error: function () {
                showAlert($form, 'An error occurred. Please try again.', 'danger');
            },
            complete: function () {
                $submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // ---------- Generic AJAX submit helper (login) ----------
    function submitForm($form, actionUrl) {
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $form.find('.alert').remove();
        $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert($form, response.message, 'success');
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                } else {
                    showAlert($form, response.message, 'danger');
                }
            },
            error: function () {
                showAlert($form, 'An error occurred. Please try again.', 'danger');
            },
            complete: function () {
                $submitBtn.html(originalText).prop('disabled', false);
            }
        });
    }

    function showAlert($form, message, type) {
        const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
        $form.prepend(`<div class="alert ${alertClass}">${message}</div>`);
    }
});
