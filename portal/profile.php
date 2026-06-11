<?php
include('config/database.php');
include('includes/auth-check.php');

// Determine which user's profile to display.
// Admins/managers can view another user's profile via ?id=, everyone else sees their own.
$viewing_user_id = $_SESSION['user_id'];
$is_other_user = false;

if (isset($_GET['id']) && hasAccess(['admin', 'manager'])) {
    $requested_id = intval($_GET['id']);
    if ($requested_id > 0 && $requested_id != $_SESSION['user_id']) {
        $viewing_user_id = $requested_id;
        $is_other_user = true;
    }
}

$conn = getDBConnection();

$user_sql = "SELECT u.*,
             (SELECT COUNT(*) FROM vehicles WHERE user_id = u.id) as vehicle_count,
             (SELECT COUNT(*) FROM services WHERE user_id = u.id) as service_count
             FROM users u WHERE u.id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $viewing_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();
$conn->close();

if (!$user_data) {
    header('Location: users-management.php');
    exit;
}

$page_title = $is_other_user
    ? htmlspecialchars($user_data['fullname']) . "'s Profile | ErrandsCall Portal"
    : "My Profile | ErrandsCall Portal";
include('includes/header.php');
include('includes/sidebar.php');
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">
            <?php echo $is_other_user ? htmlspecialchars($user_data['fullname']) . "'s Profile" : 'My Profile'; ?>
        </h1>
        <?php if ($is_other_user): ?>
        <a href="users-management.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to Users
        </a>
        <?php else: ?>
        <button class="btn btn-gradient" id="editProfileBtn">
            <i class="fas fa-edit mr-2"></i>Edit Profile
        </button>
        <?php endif; ?>
    </div>

    <!-- Profile Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number"><?php echo $user_data['vehicle_count'] ?? 0; ?></div>
                <div class="stat-label">Vehicles</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number"><?php echo $user_data['service_count'] ?? 0; ?></div>
                <div class="stat-label">Service Requests</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <div class="stat-number">
                    <span class="badge badge-<?php echo $user_data['status'] === 'active' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($user_data['status'] ?? 'active'); ?>
                    </span>
                </div>
                <div class="stat-label">Account Status</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Full Name</label>
                                <p class="form-control-plaintext"><?php echo htmlspecialchars($user_data['fullname']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Email Address</label>
                                <p class="form-control-plaintext"><?php echo htmlspecialchars($user_data['email']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Phone Number</label>
                                <p class="form-control-plaintext"><?php echo htmlspecialchars($user_data['phone'] ?? 'Not provided'); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">User Role</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-<?php echo strtolower($user_data['role']); ?>">
                                        <?php echo ucfirst($user_data['role']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Member Since</label>
                                <p class="form-control-plaintext">
                                    <?php echo date('F j, Y', strtotime($user_data['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted">Last Updated</label>
                                <p class="form-control-plaintext">
                                    <?php echo date('F j, Y g:i A', strtotime($user_data['updated_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card mt-4">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div id="profileActivity" data-user-id="<?php echo $is_other_user ? $viewing_user_id : ''; ?>">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Actions & Quick Stats -->
        <div class="col-lg-4">
            <?php if (!$is_other_user): ?>
            <!-- Profile Completion -->
            <div class="card mb-4">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Profile Completion</h5>
                </div>
                <div class="card-body">
                    <?php
                    $completion = 50; // Base completion
                    if (!empty($user_data['phone'])) $completion += 25;
                    if (!empty($user_data['address'])) $completion += 25;
                    ?>
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar bg-gradient" role="progressbar" 
                             style="width: <?php echo $completion; ?>%" 
                             aria-valuenow="<?php echo $completion; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo $completion; ?>%
                        </div>
                    </div>
                    <small class="text-muted">
                        <?php if ($completion < 100): ?>
                            Complete your profile by adding missing information.
                        <?php else: ?>
                            Your profile is complete! 🎉
                        <?php endif; ?>
                    </small>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if (isAdmin() || isManager()): ?>
                        <a href="vehicles-management.php?view=all" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-car mr-2"></i>All Vehicles
                        </a>
                        <a href="services-management.php?view=all" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-tasks mr-2"></i>All Services
                        </a>
                        <a href="users-management.php" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-users mr-2"></i>User Management
                        </a>
                        <?php elseif (isWorker()): ?>
                        <a href="service-assignments.php" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-tasks mr-2"></i>Service Assignments
                        </a>
                        <a href="chat.php" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-comments mr-2"></i>Messages
                        </a>
                        <?php elseif (isCustomer()): ?>
                        <a href="vehicles-management.php" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-car mr-2"></i>My Vehicles
                        </a>
                        <a href="services-management.php" class="btn btn-outline-primary btn-block">
                            <i class="fas fa-tasks mr-2"></i>My Service Requests
                        </a>
                        <?php endif; ?>
                        <button class="btn btn-outline-warning btn-block" id="changePasswordBtn">
                            <i class="fas fa-key mr-2"></i>Change Password
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Account Status -->
            <div class="card">
                <div class="card-header bg-gradient text-white">
                    <h5 class="mb-0">Account Status</h5>
                </div>
                <div class="card-body">
                    <?php if ($user_data['status'] === 'active'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Active</strong>
                        <p class="mb-0 small"><?php echo $is_other_user ? 'This account is in good standing.' : 'Your account is in good standing.'; ?></p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-user-slash mr-2"></i>
                        <strong>Inactive</strong>
                        <p class="mb-0 small">This account has been deactivated and cannot log in.</p>
                    </div>
                    <?php endif; ?>

                    <?php if ($is_other_user): ?>
                        <?php if (isAdmin() && $user_data['role'] !== 'admin'): ?>
                        <div class="d-grid gap-2 mt-3">
                            <?php if ($user_data['status'] === 'active'): ?>
                            <button type="button" class="btn btn-block btn-outline-danger" id="toggleStatusBtn"
                                    data-user-id="<?php echo $user_data['id']; ?>" data-current-status="active">
                                <i class="fas fa-user-slash mr-2"></i>Deactivate User
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-block btn-outline-success" id="toggleStatusBtn"
                                    data-user-id="<?php echo $user_data['id']; ?>" data-current-status="inactive">
                                <i class="fas fa-user-check mr-2"></i>Activate User
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php elseif (!isAdmin()): ?>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-outline-danger btn-block" id="deactivateAccountBtn">
                            <i class="fas fa-user-slash mr-2"></i>Deactivate Account
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$is_other_user): ?>
<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editProfileForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editFullName">Full Name *</label>
                                <input type="text" class="form-control" id="editFullName" name="fullname" 
                                       value="<?php echo htmlspecialchars($user_data['fullname']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEmail">Email Address *</label>
                                <input type="email" class="form-control" id="editEmail" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPhone">Phone Number</label>
                                <input type="tel" class="form-control" id="editPhone" name="phone" 
                                       value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAddress">Address</label>
                                <textarea class="form-control" id="editAddress" name="address" rows="2"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="currentPassword">Current Password *</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password *</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$">
                        <small class="form-text text-muted">
                            Password must be at least 8 characters long and contain both letters and numbers.
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include('includes/footer.php'); ?>

<script src="js/profile.js"></script>