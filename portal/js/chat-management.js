$(document).ready(function() {
    const rowsPerPage = 10;
    let currentPage = 1;

    // Search, filter and pagination
    if (hasChatRows()) {
        applyFiltersAndPagination();
    }

    $(document).on('click', '#chatPagination .page-link', function(e) {
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

    // Check whether the table has real data rows (vs the "No conversations found" placeholder)
    function hasChatRows() {
        return $('#chatSessionsTable tbody tr td[colspan]').length === 0;
    }

    // Get rows matching the current search term and status filter
    function getFilteredRows() {
        const searchTerm = $('#searchChats').val().toLowerCase().trim();
        const statusFilter = $('#statusFilter').val();

        return $('#chatSessionsTable tbody tr').filter(function() {
            const $row = $(this);
            const text = $row.text().toLowerCase();
            const matchesSearch = !searchTerm || text.includes(searchTerm);

            const status = $row.find('.status-badge').text().trim().toLowerCase();
            const matchesStatus = statusFilter === 'all' || status === statusFilter;

            return matchesSearch && matchesStatus;
        });
    }

    // Show the rows for the current page and rebuild the pagination control
    function applyFiltersAndPagination() {
        const $allRows = $('#chatSessionsTable tbody tr');
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
        const $pagination = $('#chatPagination');
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

    // View Conversation
    $(document).on('click', '.view-conversation', function() {
        const sessionId = $(this).data('session-id');
        const serviceId = $(this).data('service-id');
        const customer = $(this).data('customer');
        const worker = $(this).data('worker');
        const service = $(this).data('service');

        $('#viewConversationTitle').text(`${customer} ↔ ${worker}`);
        $('#viewConversationService').text(`${service} • Service ID: ${serviceId}`);
        $('#viewConversationMessages').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
        $('#viewConversationModal').modal('show');

        $.ajax({
            url: 'php/chat-management.php',
            type: 'GET',
            data: { service_id: sessionId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderConversationMessages(response.messages);
                } else {
                    $('#viewConversationMessages').html(`<p class="text-danger text-center py-4">${response.message}</p>`);
                }
            },
            error: function() {
                $('#viewConversationMessages').html('<p class="text-danger text-center py-4">Error loading conversation.</p>');
            }
        });
    });

    // Close / Reopen Conversation
    $(document).on('click', '.toggle-session', function() {
        const sessionId = $(this).data('session-id');
        const isActive = $(this).data('active') == 1;
        const action = isActive ? 'close' : 'reopen';

        if (!confirm(`Are you sure you want to ${action} this conversation?`)) {
            return;
        }

        $.ajax({
            url: 'php/chat-management.php',
            type: 'POST',
            data: { action: 'toggle_session', session_id: sessionId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert(response.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Expose for the inline onkeyup/onchange/onclick handlers in chat-management.php
    window.searchChats = function() {
        currentPage = 1;
        applyFiltersAndPagination();
    };

    window.filterByStatus = function() {
        currentPage = 1;
        applyFiltersAndPagination();
    };

    window.refreshChats = function() {
        location.reload();
    };
});

// Render the read-only message list inside the View Conversation modal
function renderConversationMessages(messages) {
    const container = $('#viewConversationMessages');

    if (!messages || messages.length === 0) {
        container.html('<p class="text-muted text-center py-4">No messages in this conversation yet.</p>');
        return;
    }

    let html = '';
    messages.forEach(msg => {
        const sideClass = msg.sender_role === 'worker' ? 'from-worker' : 'from-customer';
        const roleLabel = msg.sender_role.charAt(0).toUpperCase() + msg.sender_role.slice(1);

        html += `
            <div class="view-message ${sideClass}">
                <div class="message-content">
                    <div class="message-text">${escapeHtml(msg.message)}</div>
                    <div class="message-meta small mt-1">
                        ${escapeHtml(msg.sender_name)} (${roleLabel}) &bull; ${formatDateTime(msg.created_at)}
                    </div>
                </div>
            </div>
        `;
    });

    container.html(html);
    container.scrollTop(container[0].scrollHeight);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateTime(timestamp) {
    if (!timestamp) return '';
    return new Date(timestamp).toLocaleString();
}

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
