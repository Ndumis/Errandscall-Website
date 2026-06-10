<?php
$page_title = "Settings | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Settings</h1>
    </div>

    <div class="row">
        <!-- Settings Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#account" class="list-group-item list-group-item-action active" data-toggle="list">
                        <i class="fas fa-user-cog mr-2"></i>Account Settings
                    </a>
                    <a href="#notifications" class="list-group-item list-group-item-action" data-toggle="list">
                        <i class="fas fa-bell mr-2"></i>Notifications
                    </a>
                    <a href="#privacy" class="list-group-item list-group-item-action" data-toggle="list">
                        <i class="fas fa-shield-alt mr-2"></i>Privacy & Security
                    </a>
                    <a href="#preferences" class="list-group-item list-group-item-action" data-toggle="list">
                        <i class="fas fa-palette mr-2"></i>Preferences
                    </a>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Account Settings -->
                <div class="tab-pane fade show active" id="account">
                    <div class="card">
                        <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Account Settings</h5>
                        </div>
                        <div class="card-body">
                            <form id="accountSettingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="language">Language</label>
                                            <select class="form-control" id="language" name="language">
                                                <option value="en">English</option>
                                                <option value="es">Spanish</option>
                                                <option value="fr">French</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="timezone">Timezone</label>
                                            <select class="form-control" id="timezone" name="timezone">
                                                <option value="UTC">UTC</option>
                                                <option value="EST">Eastern Time (EST)</option>
                                                <option value="PST">Pacific Time (PST)</option>
                                                <option value="CST">Central Time (CST)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="dateFormat">Date Format</label>
                                            <select class="form-control" id="dateFormat" name="date_format">
                                                <option value="Y-m-d">YYYY-MM-DD</option>
                                                <option value="m/d/Y">MM/DD/YYYY</option>
                                                <option value="d/m/Y">DD/MM/YYYY</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="itemsPerPage">Items Per Page</label>
                                            <select class="form-control" id="itemsPerPage" name="items_per_page">
                                                <option value="10">10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="emailNotifications" name="email_notifications" checked>
                                        <label class="custom-control-label" for="emailNotifications">Email Notifications</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="smsNotifications" name="sms_notifications">
                                        <label class="custom-control-label" for="smsNotifications">SMS Notifications</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-gradient">
                                    <i class="fas fa-save mr-2"></i>Save Account Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-header bg-gradient text-white">
                            <h5 class="mb-0">Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form id="notificationSettingsForm">
                                <h6 class="text-primary mb-3">Service Notifications</h6>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="serviceUpdates" name="service_updates" checked>
                                        <label class="custom-control-label" for="serviceUpdates">Service Status Updates</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="assignmentNotifications" name="assignment_notifications" checked>
                                        <label class="custom-control-label" for="assignmentNotifications">New Assignments</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="documentUploads" name="document_uploads" checked>
                                        <label class="custom-control-label" for="documentUploads">Document Uploads</label>
                                    </div>
                                </div>
                                
                                <h6 class="text-primary mb-3 mt-4">System Notifications</h6>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="systemMaintenance" name="system_maintenance" checked>
                                        <label class="custom-control-label" for="systemMaintenance">System Maintenance</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="featureUpdates" name="feature_updates">
                                        <label class="custom-control-label" for="featureUpdates">Feature Updates</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-gradient">
                                    <i class="fas fa-save mr-2"></i>Save Notification Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Security -->
                <div class="tab-pane fade" id="privacy">
                    <div class="card">
                        <div class="card-header bg-gradient text-white">
                            <h5 class="mb-0">Privacy & Security</h5>
                        </div>
                        <div class="card-body">
                            <form id="privacySettingsForm">
                                <h6 class="text-primary mb-3">Data Privacy</h6>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="dataCollection" name="data_collection" checked>
                                        <label class="custom-control-label" for="dataCollection">Allow Anonymous Data Collection</label>
                                    </div>
                                    <small class="form-text text-muted">Help us improve by sharing anonymous usage data.</small>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="marketingEmails" name="marketing_emails">
                                        <label class="custom-control-label" for="marketingEmails">Marketing Communications</label>
                                    </div>
                                </div>
                                
                                <h6 class="text-primary mb-3 mt-4">Security</h6>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="twoFactorAuth" name="two_factor_auth">
                                        <label class="custom-control-label" for="twoFactorAuth">Two-Factor Authentication</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="loginAlerts" name="login_alerts" checked>
                                        <label class="custom-control-label" for="loginAlerts">Login Alerts</label>
                                    </div>
                                    <small class="form-text text-muted">Get notified of new sign-ins from unrecognized devices.</small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Last password change:</strong> 
                                    <span id="lastPasswordChange"><?php echo date('F j, Y g:i A'); ?></span>
                                </div>
                                
                                <button type="submit" class="btn btn-gradient">
                                    <i class="fas fa-save mr-2"></i>Save Privacy Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="tab-pane fade" id="preferences">
                    <div class="card">
                        <div class="card-header bg-gradient text-white">
                            <h5 class="mb-0">Appearance & Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form id="preferenceSettingsForm">
                                <h6 class="text-primary mb-3">Theme & Appearance</h6>
                                <div class="form-group">
                                    <label for="theme">Theme</label>
                                    <select class="form-control" id="theme" name="theme">
                                        <option value="light">Light</option>
                                        <option value="dark">Dark</option>
                                        <option value="auto">Auto (System)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="sidebarStyle">Sidebar Style</label>
                                    <select class="form-control" id="sidebarStyle" name="sidebar_style">
                                        <option value="expanded">Expanded</option>
                                        <option value="collapsed">Collapsed</option>
                                        <option value="auto">Auto-hide</option>
                                    </select>
                                </div>
                                
                                <h6 class="text-primary mb-3 mt-4">Dashboard</h6>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="showCharts" name="show_charts" checked>
                                        <label class="custom-control-label" for="showCharts">Show Statistics Charts</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="recentActivity" name="recent_activity" checked>
                                        <label class="custom-control-label" for="recentActivity">Show Recent Activity</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="quickActions" name="quick_actions" checked>
                                        <label class="custom-control-label" for="quickActions">Show Quick Actions</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-gradient">
                                    <i class="fas fa-save mr-2"></i>Save Preferences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Export Modal -->
<div class="modal fade" id="exportDataModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Your Data</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Choose the format for your data export:</p>
                <div class="form-group">
                    <div class="custom-control custom-radio">
                        <input type="radio" id="exportJSON" name="exportFormat" value="json" class="custom-control-input" checked>
                        <label class="custom-control-label" for="exportJSON">JSON Format</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="exportCSV" name="exportFormat" value="csv" class="custom-control-input">
                        <label class="custom-control-label" for="exportCSV">CSV Format</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="exportPDF" name="exportFormat" value="pdf" class="custom-control-input">
                        <label class="custom-control-label" for="exportPDF">PDF Report</label>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    The export process may take a few minutes. You'll receive a download link via email.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-gradient" id="startExport">
                    <i class="fas fa-download mr-2"></i>Start Export
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="js/settings.js"></script>