$(document).ready(function() {
    console.log('User management script loaded');

    // Form submission flag to prevent duplicates
    let isSubmitting = false;

    const rowsPerPage = 10;
    let currentPage = 1;

    // Add User Form
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;

        const password = $(this).find('[name="password"]').val();
        const confirmPassword = $(this).find('[name="confirm_password"]').val();
        if (password !== confirmPassword) {
            showAlert('Passwords do not match.', 'danger');
            return;
        }

        isSubmitting = true;
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Adding...');

        $.ajax({
            url: 'php/add-user.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);

                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#addUserModal').modal('hide');
                    $('#addUserForm')[0].reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Edit User
    $(document).on('click', '.edit-user', function() {
        const userId = $(this).data('user-id');

        $.ajax({
            url: 'php/get-user.php',
            type: 'GET',
            data: { id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const user = response.data;

                    $('#editUserId').val(user.id);
                    $('#editIdNumber').val(user.id_number);
                    $('#editRole').val(user.role);
                    $('#editFullname').val(user.fullname);
                    $('#editEmail').val(user.email);
                    $('#editPhone').val(user.phone);
                    $('#editDob').val(user.dob);
                    $('#editUserForm').find('[name="password"]').val('');
                    $('#editUserForm').find('[name="confirm_password"]').val('');

                    $('#editUserModal').modal('show');
                } else {
                    showAlert('Error loading user data.', 'danger');
                }
            },
            error: function() {
                showAlert('Error loading user data.', 'danger');
            }
        });
    });

    // Update User
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;

        const password = $(this).find('[name="password"]').val();
        const confirmPassword = $(this).find('[name="confirm_password"]').val();
        if (password !== confirmPassword) {
            showAlert('Passwords do not match.', 'danger');
            return;
        }

        isSubmitting = true;
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');

        $.ajax({
            url: 'php/update-user.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);

                if (response.success) {
                    showAlert(response.message, 'success');
                    $('#editUserModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Load user statistics
    loadUserStatistics();

    // Search, filter and pagination
    if (hasUserRows()) {
        applyFiltersAndPagination();
    }

    $(document).on('click', '#usersPagination .page-link', function(e) {
        e.preventDefault();
        const $item = $(this).closest('.page-item');
        if ($item.hasClass('disabled') || $item.hasClass('active')) {
            return;
        }
        const page = parseInt($(this).data('page'));
        if (isNaN(page) || page < 1) return;
        currentPage = page;
        applyFiltersAndPagination();
    });

    // Check whether the table has real data rows (vs the "No users found" placeholder)
    function hasUserRows() {
        return $('#usersTable tbody tr td[colspan]').length === 0;
    }

    // Get rows matching the current search term and role filter
    function getFilteredRows() {
        const searchTerm = $('#searchUsers').val().toLowerCase().trim();
        const roleFilter = $('#roleFilter').val();

        return $('#usersTable tbody tr').filter(function() {
            const $row = $(this);
            const text = $row.text().toLowerCase();
            const matchesSearch = !searchTerm || text.includes(searchTerm);

            const role = $row.find('.role-badge').text().trim().toLowerCase();
            const matchesRole = roleFilter === 'all' || role === roleFilter;

            return matchesSearch && matchesRole;
        });
    }

    // Show the rows for the current page and rebuild the pagination control
    function applyFiltersAndPagination() {
        const $allRows = $('#usersTable tbody tr');
        const $filteredRows = getFilteredRows();

        $allRows.hide();

        const totalPages = Math.max(1, Math.ceil($filteredRows.length / rowsPerPage));
        if (currentPage > totalPages) currentPage = totalPages;

        const start = (currentPage - 1) * rowsPerPage;
        $filteredRows.slice(start, start + rowsPerPage).show();

        renderPagination(totalPages, $filteredRows.length);
    }

    // Build the Bootstrap pagination markup
    function renderPagination(totalPages, totalItems) {
        const $pagination = $('#usersPagination');
        $pagination.empty();

        if (totalItems === 0 || totalPages <= 1) {
            return;
        }

        $pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `);

        for (let i = 1; i <= totalPages; i++) {
            $pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        $pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
        `);
    }

    // Load user statistics for the stat cards
    function loadUserStatistics() {
        $.ajax({
            url: 'php/get-user-statistics.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#totalCustomers').text(response.statistics.customers || 0);
                    $('#totalWorkers').text(response.statistics.workers || 0);
                    $('#totalManagers').text(response.statistics.managers || 0);
                    $('#totalAdmins').text(response.statistics.admins || 0);
                }
            }
        });
    }

    // Expose for the inline onkeyup/onchange/onclick handlers in users-management.php
    window.searchUsers = function() {
        currentPage = 1;
        applyFiltersAndPagination();
    };

    window.filterByRole = function() {
        currentPage = 1;
        applyFiltersAndPagination();
    };

    window.refreshUsers = function() {
        location.reload();
    };
});

// Show Alert
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
