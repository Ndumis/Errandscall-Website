$(document).ready(function() {
    // Load saved settings
    loadSettings();
    
    // Form submissions
    $('#accountSettingsForm').on('submit', function(e) {
        e.preventDefault();
        saveAccountSettings();
    });
    
    $('#notificationSettingsForm').on('submit', function(e) {
        e.preventDefault();
        saveNotificationSettings();
    });
    
    $('#privacySettingsForm').on('submit', function(e) {
        e.preventDefault();
        savePrivacySettings();
    });
    
    $('#preferenceSettingsForm').on('submit', function(e) {
        e.preventDefault();
        savePreferenceSettings();
    });
    
    // Data Export
    $('#startExport').on('click', function() {
        exportData();
    });
    
    // Tab persistence
    $('a[data-toggle="list"]').on('click', function() {
        const target = $(this).attr('href');
        localStorage.setItem('activeSettingsTab', target);
    });
    
    // Restore active tab
    const activeTab = localStorage.getItem('activeSettingsTab');
    if (activeTab) {
        $(`a[href="${activeTab}"]`).tab('show');
    }
});

function loadSettings() {
    $.ajax({
        url: 'php/get-user-settings.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.settings) {
                populateSettingsForm(response.settings);
            }
        },
        error: function() {
            console.error('Error loading settings');
        }
    });
}

function populateSettingsForm(settings) {
    // Account Settings
    if (settings.language) $('#language').val(settings.language);
    if (settings.timezone) $('#timezone').val(settings.timezone);
    if (settings.date_format) $('#dateFormat').val(settings.date_format);
    if (settings.items_per_page) $('#itemsPerPage').val(settings.items_per_page);
    if (settings.email_notifications !== undefined) $('#emailNotifications').prop('checked', settings.email_notifications);
    if (settings.sms_notifications !== undefined) $('#smsNotifications').prop('checked', settings.sms_notifications);
    
    // Notification Settings
    if (settings.service_updates !== undefined) $('#serviceUpdates').prop('checked', settings.service_updates);
    if (settings.assignment_notifications !== undefined) $('#assignmentNotifications').prop('checked', settings.assignment_notifications);
    if (settings.document_uploads !== undefined) $('#documentUploads').prop('checked', settings.document_uploads);
    if (settings.system_maintenance !== undefined) $('#systemMaintenance').prop('checked', settings.system_maintenance);
    if (settings.feature_updates !== undefined) $('#featureUpdates').prop('checked', settings.feature_updates);
    
    // Privacy Settings
    if (settings.data_collection !== undefined) $('#dataCollection').prop('checked', settings.data_collection);
    if (settings.marketing_emails !== undefined) $('#marketingEmails').prop('checked', settings.marketing_emails);
    if (settings.two_factor_auth !== undefined) $('#twoFactorAuth').prop('checked', settings.two_factor_auth);
    if (settings.login_alerts !== undefined) $('#loginAlerts').prop('checked', settings.login_alerts);
    
    // Preference Settings
    if (settings.theme) $('#theme').val(settings.theme);
    if (settings.sidebar_style) $('#sidebarStyle').val(settings.sidebar_style);
    if (settings.show_charts !== undefined) $('#showCharts').prop('checked', settings.show_charts);
    if (settings.recent_activity !== undefined) $('#recentActivity').prop('checked', settings.recent_activity);
    if (settings.quick_actions !== undefined) $('#quickActions').prop('checked', settings.quick_actions);
}

function saveAccountSettings() {
    const formData = new FormData($('#accountSettingsForm')[0]);

    $.ajax({
        url: 'php/save-account-settings.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Account settings saved successfully!', 'success');
            } else {
                showAlert(response.message || 'Error saving settings', 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function saveNotificationSettings() {
    const formData = new FormData($('#notificationSettingsForm')[0]);

    $.ajax({
        url: 'php/save-notification-settings.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Notification settings saved successfully!', 'success');
            } else {
                showAlert(response.message || 'Error saving settings', 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function savePrivacySettings() {
    const formData = new FormData($('#privacySettingsForm')[0]);

    $.ajax({
        url: 'php/save-privacy-settings.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Privacy settings saved successfully!', 'success');
            } else {
                showAlert(response.message || 'Error saving settings', 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function savePreferenceSettings() {
    const formData = new FormData($('#preferenceSettingsForm')[0]);

    $.ajax({
        url: 'php/save-preference-settings.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Preferences saved successfully!', 'success');
                // Apply theme changes immediately
                applyTheme($('#theme').val());
            } else {
                showAlert(response.message || 'Error saving settings', 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function exportData() {
    const format = $('input[name="exportFormat"]:checked').val();
    window.open(`php/export-user-data.php?format=${format}`, '_blank');
    $('#exportDataModal').modal('hide');
    showAlert('Your data export has started downloading.', 'success');
}

function applyTheme(theme) {
    if (theme === 'dark') {
        $('body').addClass('dark-theme');
    } else {
        $('body').removeClass('dark-theme');
    }
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