// users-management.js - Complete Fixed Version

document.addEventListener('DOMContentLoaded', function() {
    console.log('Users Management loaded');
    loadUsers();
});

// Load all users from API
function loadUsers() {
    console.log('Loading users...');
    
    fetch('../portal/php/get-all-users.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            
            // Check if response has success property and data array
            if (data.success && Array.isArray(data.data)) {
                populateUsersTable(data.data);
            } else if (Array.isArray(data)) {
                // If response is directly an array (backward compatibility)
                populateUsersTable(data);
            } else {
                console.error('Unexpected response format:', data);
                showError('Failed to load users: Invalid response format');
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showError('Failed to load users: ' + error.message);
        });
}

// Populate the users table with data
function populateUsersTable(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) {
        console.error('Users table tbody not found');
        return;
    }
    
    tbody.innerHTML = ''; // Clear existing rows
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No users found</td></tr>';
        return;
    }
    
    users.forEach(user => {
        console.log('Processing user:', user);
        
        const row = document.createElement('tr');
        
        // Safely access properties with fallbacks
        row.innerHTML = `
            <td>${user.id_number || 'N/A'}</td>
            <td>${user.fullname || 'Unknown'}</td>
            <td>${user.email || 'N/A'}</td>
            <td>${user.phone || 'N/A'}</td>
            <td><span class="role-badge role-${user.role || 'customer'}">${user.role || 'customer'}</span></td>
            <td>${formatDate(user.dob) || 'N/A'}</td>
            <td>${formatDate(user.created_at) || 'N/A'}</td>
            <td>
                <button class="btn-edit" onclick="editUser(${user.id}, this)" ${user.role === 'admin' ? 'disabled' : ''}>
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-delete" onclick="deleteUser(${user.id}, this)" ${user.role === 'admin' ? 'disabled' : ''}>
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Format date for display
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return 'Invalid Date';
    }
}

// Edit user function
function editUser(userId, buttonElement) {
    console.log('Editing user ID:', userId);
    
    // Show loading state
    const originalText = buttonElement.innerHTML;
    buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    buttonElement.disabled = true;
    
    // Fetch user details and open edit modal/form
    fetch(`../portal/php/get-user.php?id=${userId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch user details');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                openEditModal(data.data);
            } else {
                throw new Error(data.error || 'Failed to load user data');
            }
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            showError('Failed to load user details: ' + error.message);
        })
        .finally(() => {
            // Restore button state
            buttonElement.innerHTML = originalText;
            buttonElement.disabled = false;
        });
}

// Delete user function
function deleteUser(userId, buttonElement) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        console.log('Deleting user ID:', userId);
        
        // Show loading state
        const originalText = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        buttonElement.disabled = true;
        
        fetch(`../portal/php/delete-user.php?id=${userId}`, {
            method: 'DELETE',
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('User deleted successfully');
                loadUsers(); // Reload the table
            } else {
                throw new Error(data.error || 'Failed to delete user');
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            showError('Failed to delete user: ' + error.message);
        })
        .finally(() => {
            // Restore button state even if error occurred
            buttonElement.innerHTML = originalText;
            buttonElement.disabled = false;
        });
    }
}

// Open edit modal (you'll need to implement this based on your UI)
function openEditModal(userData) {
    console.log('Opening edit modal for:', userData);
    
    // Check if modal exists
    const modal = document.getElementById('editUserModal');
    if (modal) {
        // Safely populate modal fields
        const fields = {
            'editUserId': userData.id,
            'editFullName': userData.fullname || '',
            'editEmail': userData.email || '',
            'editPhone': userData.phone || '',
            'editRole': userData.role || 'customer',
            'editDob': userData.dob || ''
        };
        
        // Populate each field only if it exists
        Object.keys(fields).forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = fields[fieldId];
            } else {
                console.warn(`Modal field #${fieldId} not found`);
            }
        });
        
        // Show modal (using Bootstrap if available)
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modal).modal('show');
        } else {
            modal.style.display = 'block';
        }
    } else {
        console.log('Edit modal not found, using simple edit form');
        // Create a simple edit form since modal doesn't exist
        showSimpleEditForm(userData);
    }
}

// Simple edit form as fallback
function showSimpleEditForm(userData) {
    const formHtml = `
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;">
            <div style="background: white; padding: 20px; border-radius: 8px; width: 400px; max-width: 90%;">
                <h3>Edit User</h3>
                <form id="simpleEditForm">
                    <input type="hidden" name="id" value="${userData.id}">
                    <div style="margin-bottom: 10px;">
                        <label>Full Name:</label>
                        <input type="text" name="fullname" value="${userData.fullname || ''}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Email:</label>
                        <input type="email" name="email" value="${userData.email || ''}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Phone:</label>
                        <input type="text" name="phone" value="${userData.phone || ''}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label>Role:</label>
                        <select name="role" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="customer" ${userData.role === 'customer' ? 'selected' : ''}>Customer</option>
                            <option value="worker" ${userData.role === 'worker' ? 'selected' : ''}>Worker</option>
                            <option value="manager" ${userData.role === 'manager' ? 'selected' : ''}>Manager</option>
                            <option value="admin" ${userData.role === 'admin' ? 'selected' : ''}>Admin</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label>Date of Birth:</label>
                        <input type="date" name="dob" value="${userData.dob || ''}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" onclick="closeSimpleEditForm()" style="padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                        <button type="submit" style="padding: 8px 16px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    const overlay = document.createElement('div');
    overlay.innerHTML = formHtml;
    overlay.id = 'simpleEditOverlay';
    document.body.appendChild(overlay);
    
    // Add form submit handler
    document.getElementById('simpleEditForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveUserChanges(new FormData(this));
    });
}

// Close simple edit form
function closeSimpleEditForm() {
    const overlay = document.getElementById('simpleEditOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Save user changes
function saveUserChanges(formData) {
    console.log('Saving user changes...');
    // Implement your save logic here
    showSuccess('User updated successfully (save functionality to be implemented)');
    closeSimpleEditForm();
}

// Search/filter users function
function searchUsers() {
    const searchTerm = document.getElementById('searchUsers').value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Filter users by role
function filterByRole(role) {
    const rows = document.querySelectorAll('#usersTable tbody tr');
    
    rows.forEach(row => {
        if (role === 'all') {
            row.style.display = '';
        } else {
            const roleBadge = row.querySelector('.role-badge');
            const userRole = roleBadge ? roleBadge.textContent.toLowerCase() : '';
            row.style.display = userRole === role ? '' : 'none';
        }
    });
}

// Refresh users data
function refreshUsers() {
    console.log('Refreshing users data...');
    loadUsers();
}

// Utility functions for showing messages
function showError(message) {
    console.error('Error:', message);
    
    // Check if Toastify is available
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
        }).showToast();
    } else {
        // Fallback to alert
        alert('Error: ' + message);
    }
}

function showSuccess(message) {
    console.log('Success:', message);
    
    // Check if Toastify is available
    if (typeof Toastify !== 'undefined') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
        }).showToast();
    } else {
        // Fallback to alert
        alert('Success: ' + message);
    }
}

// Add CSS styles dynamically
function initializeStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .role-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }
        .role-admin { 
            background: #ff4757; 
            color: white; 
        }
        .role-manager { 
            background: #3742fa; 
            color: white; 
        }
        .role-worker { 
            background: #2ed573; 
            color: white; 
        }
        .role-customer { 
            background: #747d8c; 
            color: white; 
        }
        
        .btn-edit, .btn-delete {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background-color: #3498db;
            color: white;
        }
        
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-edit:hover:not(:disabled) {
            background-color: #2980b9;
            transform: translateY(-1px);
        }
        
        .btn-delete:hover:not(:disabled) {
            background-color: #c0392b;
            transform: translateY(-1px);
        }
        
        .btn-edit:disabled, .btn-delete:disabled {
            background-color: #bdc3c7;
            cursor: not-allowed;
            opacity: 0.6;
            transform: none;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Loading spinner */
        .fa-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Search and filter styles */
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input, .filter-box select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .action-buttons {
            margin-bottom: 20px;
        }
        
        .btn-refresh {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-refresh:hover {
            background-color: #219a52;
        }
    `;
    document.head.appendChild(style);
}

// Initialize styles when the script loads
initializeStyles();