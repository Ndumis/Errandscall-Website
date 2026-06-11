$(document).ready(function() {
    // Load profile activity
    loadProfileActivity();
    
    // Edit Profile Modal
    $('#editProfileBtn').on('click', function() {
        $('#editProfileModal').modal('show');
    });
    
    // Change Password Modal
    $('#changePasswordBtn').on('click', function() {
        $('#changePasswordModal').modal('show');
    });
    
    // Edit Profile Form
    $('#editProfileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    // Change Password Form
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        changePassword();
    });
    
    // Deactivate Account
    $('#deactivateAccountBtn').on('click', function() {
        if (confirm('Are you sure you want to deactivate your account? You can reactivate it later by logging in.')) {
            deactivateAccount();
        }
    });

    // Activate / Deactivate another user (admin viewing a user's profile)
    $('#toggleStatusBtn').on('click', function() {
        const userId = $(this).data('user-id');
        const currentStatus = $(this).data('current-status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'deactivate';

        if (!confirm(`Are you sure you want to ${action} this user?`)) {
            return;
        }

        $.ajax({
            url: 'php/toggle-user-status.php',
            type: 'POST',
            data: { user_id: userId, status: newStatus },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });
});

function loadProfileActivity() {
    const targetUserId = $('#profileActivity').data('user-id');
    $.ajax({
        url: 'php/get-profile-activity.php',
        type: 'GET',
        data: targetUserId ? { id: targetUserId } : {},
        dataType: 'json',
        success: function(response) {
            if (response.success && response.activities) {
                let html = '';
                if (response.activities.length > 0) {
                    response.activities.forEach(activity => {
                        html += `
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                <div>
                                    <strong>${escapeHtml(activity.action || 'Activity')}</strong>
                                    <br>
                                    <small class="text-muted">${escapeHtml(activity.description || '')}</small>
                                </div>
                                <small class="text-muted">${formatTime(activity.created_at)}</small>
                            </div>
                        `;
                    });
                } else {
                    html = '<p class="text-muted text-center">No recent activity found</p>';
                }
                $('#profileActivity').html(html);
            } else {
                $('#profileActivity').html('<p class="text-muted text-center">No activity data available</p>');
            }
        },
        error: function() {
            $('#profileActivity').html('<p class="text-danger">Error loading activity</p>');
        }
    });
}

function updateProfile() {
    const formData = new FormData($('#editProfileForm')[0]);
    
    $.ajax({
        url: 'php/update-profile.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Profile updated successfully!', 'success');
                $('#editProfileModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(response.message || 'Error updating profile', 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function changePassword() {
    const formData = new FormData($('#changePasswordForm')[0]);
    
    // Basic validation
    const newPassword = $('#newPassword').val();
    const confirmPassword = $('#confirmPassword').val();
    
    if (newPassword !== confirmPassword) {
        showAlert('New passwords do not match!', 'danger');
        return;
    }
    
    if (newPassword.length < 8) {
        showAlert('Password must be at least 8 characters long!', 'danger');
        return;
    }
    
    $.ajax({
        url: 'php/change-password.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Password changed successfully!', 'success');
                $('#changePasswordModal').modal('hide');
                $('#changePasswordForm')[0].reset();
            } else {
                showAlert(response.message || 'Error changing password', 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function deactivateAccount() {
    if (!confirm('This will deactivate your account. Are you absolutely sure?')) {
        return;
    }
    
    $.ajax({
        url: 'php/deactivate-account.php',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Account deactivated successfully. You will be logged out.', 'success');
                setTimeout(() => window.location.href = 'php/logout.php', 2000);
            } else {
                showAlert(response.message || 'Error deactivating account', 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function formatTime(dateString) {
    if (!dateString) return 'Unknown time';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'Invalid date';
    
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString();
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = $(`<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>`);
    
    $('.main-content').prepend(alert);
    
    setTimeout(() => {
        alert.alert('close');
    }, 5000);
}