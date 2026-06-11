<?php
$page_title = "User Management | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');

// Only admin and manager can access this page
if (!hasAccess(['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit;
}

include('includes/header.php');
include('includes/sidebar.php');

// Get users based on role
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM users ORDER BY role, fullname");
$stmt->execute();
$users_result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">User Management</h1>
        <?php if (isAdmin()): ?>
        <button class="btn btn-gradient" data-toggle="modal" data-target="#addUserModal">
            <i class="fas fa-user-plus mr-2"></i>Add New User
        </button>
        <?php endif; ?>
    </div>
	
	<!-- Search and Filter -->
	<div class="row mb-3">
		<div class="col-md-5 col-lg-4 mb-2 mb-md-0">
			<input type="text" class="form-control" id="searchUsers" placeholder="Search users..." onkeyup="searchUsers()">
		</div>
		<div class="col-md-4 col-lg-3 mb-2 mb-md-0">
			<select class="form-control" id="roleFilter" onchange="filterByRole(this.value)">
				<option value="all">All Roles</option>
				<option value="admin">Admin</option>
				<option value="manager">Manager</option>
				<option value="worker">Worker</option>
				<option value="customer">Customer</option>
			</select>
		</div>
		<div class="col-md-3 col-lg-2">
			<button type="button" class="btn btn-outline-primary btn-block" onclick="refreshUsers()">
				<i class="fas fa-sync-alt mr-1"></i>Refresh
			</button>
		</div>
	</div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header bg-gradient text-white">
            <h5 class="mb-0">All Users</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Date of Birth</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users_result->num_rows > 0): ?>
                            <?php while($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id_number']; ?></td>
                                <td>
                                    <?php echo $user['fullname']; ?>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge badge-info ml-1">You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['phone']; ?></td>
                                <td>
                                    <span class="badge role-badge badge-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['dob'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="profile.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-outline-primary edit-user" data-user-id="<?php echo $user['id']; ?>"
                                                <?php echo ($user['id'] == $_SESSION['user_id'] || ($user['role'] == 'admin' && !isAdmin())) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">No users found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Users pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="usersPagination"></ul>
            </nav>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="totalCustomers">0</div>
                <div class="stat-label">Customers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="totalWorkers">0</div>
                <div class="stat-label">Workers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="totalManagers">0</div>
                <div class="stat-label">Managers</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number" id="totalAdmins">0</div>
                <div class="stat-label">Admins</div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>ID Number *</label>
                            <input type="text" class="form-control" name="id_number" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Role *</label>
                            <select class="form-control" name="role" required>
                                <option value="">Select Role</option>
                                <option value="worker">Worker</option>
                                <option value="manager">Manager</option>
                                <?php if (isAdmin()): ?>
                                <option value="admin">Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" class="form-control" name="fullname" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Email *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Phone *</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" class="form-control" name="dob" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Confirm Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            The user will be able to login with their Id Number or Email address.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="editUserId">
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>ID Number *</label>
                            <input type="text" class="form-control" name="id_number" id="editIdNumber" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Role *</label>
                            <select class="form-control" name="role" id="editRole" required>
                                <option value="">Select Role</option>
                                <option value="customer">Customer</option>
                                <option value="worker">Worker</option>
                                <option value="manager">Manager</option>
                                <?php if (isAdmin()): ?>
                                <option value="admin">Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" class="form-control" name="fullname" id="editFullname" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Email *</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Phone *</label>
                            <input type="text" class="form-control" name="phone" id="editPhone" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" class="form-control" name="dob" id="editDob" required>
                    </div>

                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password">
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="js/users-management.js"></script>