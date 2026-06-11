<?php
$page_title = "Chat Management | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');

// Only admin and manager can access this page
if (!hasAccess(['admin', 'manager'])) {
    header('Location: dashboard.php');
    exit;
}

include('includes/header.php');
include('includes/sidebar.php');

$conn = getDBConnection();

// Conversation statistics
$stats_result = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM chat_sessions WHERE is_active = TRUE) as active_count,
        (SELECT COUNT(*) FROM chat_sessions WHERE is_active = FALSE) as closed_count,
        (SELECT COUNT(*) FROM chat_messages) as total_messages,
        (SELECT COUNT(*) FROM chat_messages WHERE DATE(created_at) = CURDATE()) as messages_today
");
$stats = $stats_result->fetch_assoc();

// All chat sessions with related service, vehicle and user details
$sessions_result = $conn->query("
    SELECT
        cs.*,
        s.service_type,
        v.make, v.model, v.license_plate,
        u_customer.fullname as customer_name,
        u_worker.fullname as worker_name,
        (SELECT COUNT(*) FROM chat_messages cm WHERE cm.chat_session_id = cs.id) as message_count,
        (SELECT message FROM chat_messages cm WHERE cm.chat_session_id = cs.id ORDER BY cm.created_at DESC LIMIT 1) as last_message
    FROM chat_sessions cs
    JOIN services s ON cs.service_id = s.id
    JOIN vehicles v ON s.vehicle_id = v.id
    JOIN users u_customer ON cs.customer_id = u_customer.id
    JOIN users u_worker ON cs.worker_id = u_worker.id
    ORDER BY cs.last_message_at DESC
");
$conn->close();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Chat Management</h1>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-3">
        <div class="col-md-5 col-lg-4 mb-2 mb-md-0">
            <input type="text" class="form-control" id="searchChats" placeholder="Search conversations..." onkeyup="searchChats()">
        </div>
        <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
            <select class="form-control" id="statusFilter" onchange="filterByStatus(this.value)">
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <button type="button" class="btn btn-outline-primary btn-block" onclick="refreshChats()">
                <i class="fas fa-sync-alt mr-1"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Conversations Table -->
    <div class="card">
        <div class="card-header bg-gradient text-white">
            <h5 class="mb-0">All Conversations</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="chatSessionsTable">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Worker</th>
                            <th>Service</th>
                            <th>Last Message</th>
                            <th>Last Activity</th>
                            <th>Messages</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sessions_result->num_rows > 0): ?>
                            <?php while ($session = $sessions_result->fetch_assoc()): ?>
                            <?php
                                $last_message = $session['last_message'];
                                $last_message_preview = $last_message
                                    ? mb_substr($last_message, 0, 50) . (mb_strlen($last_message) > 50 ? '...' : '')
                                    : 'No messages yet';
                                $last_activity = $session['last_message_at'] ?: $session['created_at'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($session['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($session['worker_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($session['service_type']); ?>
                                    <small class="text-muted d-block">
                                        <?php echo htmlspecialchars($session['make'] . ' ' . $session['model'] . ' (' . $session['license_plate'] . ')'); ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($last_message_preview); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($last_activity)); ?></td>
                                <td><span class="badge badge-secondary"><?php echo $session['message_count']; ?></span></td>
                                <td>
                                    <span class="badge status-badge <?php echo $session['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $session['is_active'] ? 'Active' : 'Closed'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info view-conversation"
                                                data-session-id="<?php echo $session['id']; ?>"
                                                data-service-id="<?php echo $session['service_id']; ?>"
                                                data-customer="<?php echo htmlspecialchars($session['customer_name']); ?>"
                                                data-worker="<?php echo htmlspecialchars($session['worker_name']); ?>"
                                                data-service="<?php echo htmlspecialchars($session['service_type']); ?>"
                                                title="View Conversation">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($session['is_active']): ?>
                                        <button type="button" class="btn btn-outline-warning toggle-session"
                                                data-session-id="<?php echo $session['id']; ?>" data-active="1" title="Close Conversation">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-outline-success toggle-session"
                                                data-session-id="<?php echo $session['id']; ?>" data-active="0" title="Reopen Conversation">
                                            <i class="fas fa-lock-open"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-comments fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">No conversations found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Conversations pagination">
                <ul class="pagination justify-content-center mb-0 mt-3" id="chatPagination"></ul>
            </nav>
        </div>
    </div>

    <!-- Conversation Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['active_count']; ?></div>
                <div class="stat-label">Active Conversations</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['closed_count']; ?></div>
                <div class="stat-label">Closed Conversations</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_messages']; ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['messages_today']; ?></div>
                <div class="stat-label">Messages Today</div>
            </div>
        </div>
    </div>
</div>

<!-- View Conversation Modal -->
<div class="modal fade" id="viewConversationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewConversationTitle">Conversation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="viewConversationService"></p>
                <div id="viewConversationMessages">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
#viewConversationMessages {
    max-height: 400px;
    overflow-y: auto;
}

.view-message {
    display: flex;
    margin-bottom: 1rem;
}

.view-message.from-worker {
    justify-content: flex-end;
}

.view-message .message-content {
    max-width: 75%;
    padding: 0.75rem 1rem;
    border-radius: 12px;
}

.view-message.from-customer .message-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-top-left-radius: 4px;
}

.view-message.from-worker .message-content {
    background: var(--primary-gradient);
    color: white;
    border-top-right-radius: 4px;
}

.view-message .message-meta {
    opacity: 0.8;
}
</style>

<?php include('includes/footer.php'); ?>

<script src="js/chat-management.js"></script>
