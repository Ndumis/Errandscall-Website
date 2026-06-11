<?php
$page_title = "Chat | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user's services that can have chats
if (hasAccess(['customer'])) {
    $services_stmt = $conn->prepare("
        SELECT s.id, s.service_type, s.status, v.make, v.model, v.license_plate,
               u.fullname as worker_name,
               cs.id as chat_session_id
        FROM services s
        JOIN vehicles v ON s.vehicle_id = v.id
        LEFT JOIN users u ON s.assigned_to = u.id
        LEFT JOIN chat_sessions cs ON s.id = cs.service_id
        WHERE s.user_id = ? AND s.status IN ('assigned', 'in_progress')
        ORDER BY s.created_at DESC
    ");
    $services_stmt->bind_param("i", $user_id);
} else if (hasAccess(['worker'])) {
    $services_stmt = $conn->prepare("
        SELECT s.id, s.service_type, s.status, v.make, v.model, v.license_plate,
               u.fullname as customer_name,
               cs.id as chat_session_id
        FROM services s
        JOIN vehicles v ON s.vehicle_id = v.id
        JOIN users u ON s.user_id = u.id
        LEFT JOIN chat_sessions cs ON s.id = cs.service_id
        WHERE s.assigned_to = ? AND s.status IN ('assigned', 'in_progress')
        ORDER BY s.created_at DESC
    ");
    $services_stmt->bind_param("i", $user_id);
} else {
    // Admin/manager - all active services
    $services_stmt = $conn->prepare("
        SELECT s.id, s.service_type, s.status, v.make, v.model, v.license_plate,
               u_customer.fullname as customer_name,
               u_worker.fullname as worker_name,
               cs.id as chat_session_id
        FROM services s
        JOIN vehicles v ON s.vehicle_id = v.id
        JOIN users u_customer ON s.user_id = u_customer.id
        LEFT JOIN users u_worker ON s.assigned_to = u_worker.id
        LEFT JOIN chat_sessions cs ON s.id = cs.service_id
        WHERE s.status IN ('assigned', 'in_progress')
        ORDER BY s.created_at DESC
    ");
}

$services_stmt->execute();
$services_result = $services_stmt->get_result();
$services = [];
while ($row = $services_result->fetch_assoc()) {
    $services[] = $row;
}
$services_stmt->close();
$conn->close();
?>
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient text-white">
                        <h5 class="mb-0">Service Chat</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Services List & Conversations -->
                            <div class="col-md-4 border-right">
                                <div class="services-chat-list">
                                    <!-- Available Services for Chat -->
									<?php if (hasAccess(['customer', 'worker'])): ?>
									<div class="mb-4">
										<h6 class="text-primary mb-3">
											<?php echo hasAccess(['customer']) ? 'My Active Services' : 'My Assigned Services'; ?>
										</h6>
										<div id="availableServices">
											<?php if (empty($services)): ?>
												<div class="text-center py-3">
													<i class="fas fa-tasks fa-2x text-muted mb-2"></i>
													<p class="text-muted small">No active services available for chat</p>
												</div>
											<?php else: ?>
												<?php foreach ($services as $service): ?>
													<div class="service-item p-3 border rounded mb-2">
														<div class="d-flex justify-content-between align-items-start">
															<div class="flex-grow-1">
																<h6 class="mb-1"><?php echo htmlspecialchars($service['service_type']); ?></h6>
																<small class="text-muted d-block">
																	<?php echo htmlspecialchars($service['make'] . ' ' . $service['model'] . ' (' . $service['license_plate'] . ')'); ?>
																</small>
																<small class="text-muted">
																	Status: <span class="badge badge-info"><?php echo ucfirst($service['status']); ?></span>
																</small>
																<?php if (hasAccess(['customer']) && $service['worker_name']): ?>
																<small class="text-muted d-block">
																	Assigned to: <?php echo htmlspecialchars($service['worker_name']); ?>
																</small>
																<?php endif; ?>
																<?php if (hasAccess(['worker']) && $service['customer_name']): ?>
																<small class="text-muted d-block">
																	Customer: <?php echo htmlspecialchars($service['customer_name']); ?>
																</small>
																<?php endif; ?>
															</div>
														</div>
														<div class="mt-2">
															<?php if ($service['chat_session_id']): ?>
																<button class="btn btn-sm btn-outline-primary w-100" 
																		onclick="selectChatSession(this, <?php echo $service['chat_session_id']; ?>, '<?php echo hasAccess(['customer']) ? htmlspecialchars($service['worker_name']) : htmlspecialchars($service['customer_name']); ?>', <?php echo $service['id']; ?>)">
																	<i class="fas fa-comments mr-1"></i> Open Chat
																</button>
															<?php else: ?>
																<button class="btn btn-sm btn-gradient w-100" 
																		onclick="startChatSession(<?php echo $service['id']; ?>)">
																	<i class="fas fa-plus mr-1"></i> Start Chat
																</button>
															<?php endif; ?>
														</div>
													</div>
												<?php endforeach; ?>
											<?php endif; ?>
										</div>
									</div>
									<?php endif; ?>
                                    
                                    <!-- Active Conversations -->
                                    <div>
                                        <h6 class="text-primary mb-3">Active Conversations</h6>
                                        <div id="conversationsContainer" style="max-height: 400px; overflow-y: auto;">
                                            <!-- Conversations will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Chat Messages -->
                            <div class="col-md-8">
                                <div class="chat-container">
                                    <div id="selectedConversation" class="text-center text-muted py-5">
                                        <i class="fas fa-comments fa-3x mb-3"></i>
                                        <p>Select a service or conversation to start chatting</p>
                                        <small class="text-muted">Chat is available for assigned and in-progress services</small>
                                    </div>
                                    <div id="activeChat" style="display: none;">
                                        <div class="chat-header border-bottom pb-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 id="chatWithUser" class="mb-1"></h6>
                                                    <small class="text-muted" id="chatServiceInfo"></small>
                                                </div>
                                                <div class="chat-actions">
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="refreshChat()">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="chatMessages" style="height: 400px; overflow-y: auto; padding: 1rem;"></div>
                                        <div class="chat-input mt-3">
                                            <div class="input-group">
                                                <textarea class="form-control" id="messageInput" placeholder="Type your message..." rows="2"></textarea>
                                                <div class="input-group-append">
                                                    <button class="btn btn-gradient" id="sendMessageBtn">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted mt-1">
                                                Press Enter to send, Shift+Enter for new line
                                            </small>
                                        </div>
                                        <div id="chatReadOnlyNotice" class="alert alert-info mt-3 mb-0" style="display: none;">
                                            <i class="fas fa-eye mr-2"></i>You're viewing this conversation as <?php echo ucfirst($_SESSION['user_role']); ?>. Conversations between customers and workers are read-only here.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chat JavaScript -->
<script>
let currentChatSessionId = null;
let currentServiceId = null;
let chatPollingInterval = null;
const currentUserRole = '<?php echo $_SESSION['user_role']; ?>';

function hasAccess(roles) {
    return roles.includes(currentUserRole);
}

document.addEventListener('DOMContentLoaded', function() {
    loadConversations();
    
    // Send message
    document.getElementById('sendMessageBtn').addEventListener('click', sendMessage);
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
});

function loadConversations() {
    fetch('php/chat-management.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayConversations(data.sessions);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayConversations(sessions) {
    const container = document.getElementById('conversationsContainer');
    
    if (!sessions || sessions.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                <p class="text-muted small">No active conversations</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    sessions.forEach(session => {
        const unreadBadge = session.unread_count > 0 ?
            `<span class="badge badge-primary ml-2">${session.unread_count}</span>` : '';

        const otherParty = getOtherPartyName(session);

        const lastMessagePreview = session.last_message ?
            session.last_message.substring(0, 50) + (session.last_message.length > 50 ? '...' : '') :
            'No messages yet';

        html += `
            <div class="conversation-item d-flex align-items-center p-3 border-bottom"
                 data-session-id="${session.id}" data-service-id="${session.service_id}" data-other-party="${escapeHtml(otherParty)}"
                 style="cursor: pointer;">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${escapeHtml(otherParty)}</h6>
                    <small class="text-muted d-block">${escapeHtml(session.service_type)}</small>
                    <small class="text-muted">${escapeHtml(lastMessagePreview)}</small>
                    <br>
                    <small class="text-muted">${formatTime(session.last_message_time)}</small>
                </div>
                ${unreadBadge}
            </div>
        `;
    });

    container.innerHTML = html;

    container.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', function() {
            selectChatSession(this, parseInt(this.dataset.sessionId, 10), this.dataset.otherParty, parseInt(this.dataset.serviceId, 10));
        });
        if (currentChatSessionId && parseInt(item.dataset.sessionId, 10) === currentChatSessionId) {
            item.classList.add('active');
        }
    });
}

function getOtherPartyName(session) {
    if (hasAccess(['customer'])) {
        return session.worker_name || 'Worker';
    } else if (hasAccess(['worker'])) {
        return session.customer_name || 'Customer';
    }
    // Admin/manager view
    return `${session.customer_name} ↔ ${session.worker_name}`;
}

function startChatSession(serviceId) {
    if (!confirm('Start a chat for this service? The assigned worker will be notified.')) {
        return;
    }
    
    fetch(`php/chat-management.php?start_chat=${serviceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Chat session started!', 'success');
                loadConversations();
                // Automatically open the new chat session
                setTimeout(() => {
                    const newItem = document.querySelector(`.conversation-item[data-session-id="${data.chat_session_id}"]`);
                    const otherParty = newItem ? newItem.dataset.otherParty : '';
                    selectChatSession(newItem, data.chat_session_id, otherParty, serviceId);
                }, 500);
            } else {
                showAlert('Error: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error starting chat session', 'error');
            console.error('Error:', error);
        });
}

function selectChatSession(element, chatSessionId, otherPartyName, serviceId) {
    currentChatSessionId = chatSessionId;
    currentServiceId = serviceId;

    document.getElementById('selectedConversation').style.display = 'none';
    document.getElementById('activeChat').style.display = 'block';
    document.getElementById('chatWithUser').textContent = `Chat with ${otherPartyName}`;
    document.getElementById('chatServiceInfo').textContent = `Service ID: ${serviceId}`;

    // Admins/managers can monitor conversations but cannot send messages
    const canSend = hasAccess(['customer', 'worker']);
    document.querySelector('.chat-input').style.display = canSend ? 'block' : 'none';
    document.getElementById('chatReadOnlyNotice').style.display = canSend ? 'none' : 'block';

    loadChatMessages(chatSessionId);
    startChatPolling(chatSessionId);

    // Highlight selected conversation/service
    document.querySelectorAll('.conversation-item, .service-item').forEach(item => {
        item.classList.remove('active');
    });

    const target = element ? element.closest('.conversation-item, .service-item')
                            : document.querySelector(`.conversation-item[data-session-id="${chatSessionId}"]`);
    if (target) {
        target.classList.add('active');
    }
}

function loadChatMessages(chatSessionId) {
    fetch(`php/chat-management.php?service_id=${chatSessionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayMessages(messages) {
    const container = document.getElementById('chatMessages');
    let html = '';
    
    messages.forEach(msg => {
        const messageClass = msg.is_own_message ? 'own-message' : 'other-message';
        const time = formatTime(msg.created_at);
        
        html += `
            <div class="chat-message ${messageClass} mb-3">
                <div class="message-content p-3 rounded">
                    <div class="message-text">${escapeHtml(msg.message)}</div>
                    <div class="message-time text-muted small mt-1">
                        ${escapeHtml(msg.sender_name)} • ${time}
                        ${msg.is_own_message ? '<i class="fas fa-check ml-1 ' + (msg.is_read ? 'text-primary' : 'text-muted') + '"></i>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    container.scrollTop = container.scrollHeight;
}

function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message || !currentChatSessionId) {
        showAlert('Please select a chat and enter a message', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('chat_session_id', currentChatSessionId);
    formData.append('message', message);
    
    fetch('php/chat-management.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadChatMessages(currentChatSessionId);
            loadConversations(); // Update conversation list
        } else {
            showAlert('Error sending message: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error sending message', 'error');
        console.error('Error:', error);
    });
}

function startChatPolling(chatSessionId) {
    // Clear existing interval
    if (chatPollingInterval) {
        clearInterval(chatPollingInterval);
    }
    
    // Poll for new messages every 3 seconds
    chatPollingInterval = setInterval(() => {
        if (currentChatSessionId === chatSessionId) {
            loadChatMessages(chatSessionId);
            loadConversations(); // Update conversation list and unread counts
        }
    }, 3000);
}

function refreshChat() {
    if (currentChatSessionId) {
        loadChatMessages(currentChatSessionId);
        showAlert('Chat refreshed', 'info');
    }
}

function formatTime(timestamp) {
    if (!timestamp) return 'Never';
    
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff/60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff/3600000) + 'h ago';
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.querySelector('.main-content').insertBefore(alert, document.querySelector('.container-fluid'));
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>

<style>
.chat-message.own-message {
    text-align: right;
}

.chat-message.own-message .message-content {
    background: var(--primary-gradient);
    color: white;
    margin-left: 20%;
    border-bottom-right-radius: 4px;
    border-top-right-radius: 12px;
}

.chat-message.other-message .message-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    margin-right: 20%;
    border-bottom-left-radius: 4px;
    border-top-left-radius: 12px;
}

.conversation-item:hover, .service-item:hover {
    background-color: #f8f9fa;
}

.conversation-item.active, .service-item.active {
    background-color: rgba(255, 140, 0, 0.1);
    border-left: 3px solid var(--primary-color);
}

.service-item {
    transition: all 0.3s ease;
}

.service-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>

<?php include('includes/footer.php'); ?>