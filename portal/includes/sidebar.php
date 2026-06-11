<?php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Sidebar -->
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <h5>Navigation</h5>
    </div>
    
    <ul class="list-unstyled components">
        <li>
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt mr-2"></i>
                Dashboard
            </a>
        </li>
        
        <?php if (hasAccess(['admin', 'manager', 'customer'])): ?>
        <li>
            <a href="#vehiclesSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-car mr-2"></i>
                Vehicles
            </a>
            <ul class="collapse list-unstyled" id="vehiclesSubmenu">
                <?php if (hasAccess(['customer'])): ?>
                <li>
                    <a href="vehicles-management.php">My Vehicles</a>
                </li>
                <?php endif; ?>
                <?php if (hasAccess(['admin', 'manager'])): ?>
                <li>
                    <a href="vehicles-management.php?view=all">All Vehicles</a>
                </li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>
        
        <?php if (hasAccess(['admin', 'manager', 'worker', 'customer'])): ?>
        <li>
            <a href="#servicesSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-tasks mr-2"></i>
                Services Management
            </a>
            <ul class="collapse list-unstyled" id="servicesSubmenu">
                <?php if (hasAccess(['customer'])): ?>
                <li>
                    <a href="services-management.php">My Service Requests</a>
                </li>
                <?php endif; ?>
                <?php if (hasAccess(['admin', 'manager'])): ?>
                <li>
                    <a href="services-management.php?view=all">All Services</a>
                </li>
                <?php endif; ?>
                <?php if (hasAccess(['worker'])): ?>
                <li>
                    <a href="service-assignments.php">Service Assignments</a>
                </li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>
        
		<!-- User Management -->
        <?php if (hasAccess(['admin', 'manager'])): ?>
        <li>
            <a href="#usersSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-users mr-2"></i>
                User Management
            </a>
            <ul class="collapse list-unstyled" id="usersSubmenu">
                <li>
                    <a href="users-management.php">All Users</a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
		
        <!-- Real-time Chat -->
        <?php if (hasAccess(['admin', 'manager', 'worker', 'customer'])): ?>
        <li>
            <a href="#chatSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-comments mr-2"></i>
                Messages
                <span class="badge badge-primary badge-pill ml-2" id="unreadMessagesCount">0</span>
            </a>
            <ul class="collapse list-unstyled" id="chatSubmenu">
                <li>
                    <a href="chat.php">My Conversations</a>
                </li>
                <?php if (hasAccess(['admin', 'manager'])): ?>
                <li>
                    <a href="chat-management.php">All Conversations</a>
                </li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>
        
        <!-- GPS Tracking -->
        <?php if (hasAccess(['admin', 'manager'])): ?>
        <li>
            <a href="#trackingSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-map-marker-alt mr-2"></i>
                Tracking
            </a>
            <ul class="collapse list-unstyled" id="trackingSubmenu">
                <?php if (hasAccess(['admin', 'manager'])): ?>
                <li>
                    <a href="live-tracking.php">Live Tracking</a>
                </li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>
        
        <!-- Analytics & Reports -->
        <?php if (hasAccess(['admin', 'manager'])): ?>
        <li>
            <a href="#analyticsSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-chart-bar mr-2"></i>
                Analytics & Reports
            </a>
            <ul class="collapse list-unstyled" id="analyticsSubmenu">
                <li>
                    <a href="analytics-management.php">Analytics Dashboard</a>
                </li>
                <li>
                    <a href="service-reports.php">Service Reports</a>
                </li>
                <li>
                    <a href="vehicle-reports.php">Vehicle Reports</a>
                </li>
                <li>
                    <a href="user-reports.php">User Reports</a>
                </li>
                <li>
                    <a href="performance-reports.php">Performance Reports</a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
    </ul>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer mt-auto p-3">
        <div class="text-center">
            <small class="text-muted">ErrandsCall Portal</small>
            <br>
            <small class="text-muted">v2.0</small>
        </div>
    </div>
</nav>

<!-- JavaScript for dynamic badge updates -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to update unread message count
    function updateUnreadCounts() {
        // Fetch unread messages count
        fetch('php/get-unread-counts.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const messagesBadge = document.getElementById('unreadMessagesCount');
                    if (messagesBadge) {
                        if (data.unreadMessages > 0) {
                            messagesBadge.textContent = data.unreadMessages;
                            messagesBadge.style.display = 'inline';
                        } else {
                            messagesBadge.style.display = 'none';
                        }
                    }

                    const notificationsBadge = document.getElementById('notificationCount');
                    if (notificationsBadge) {
                        if (data.unreadNotifications > 0) {
                            notificationsBadge.textContent = data.unreadNotifications;
                            notificationsBadge.style.display = 'inline';
                        } else {
                            notificationsBadge.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => console.error('Error fetching unread counts:', error));
    }
    
    // Update counts on page load
    updateUnreadCounts();
    
    // Update counts every 30 seconds
    setInterval(updateUnreadCounts, 30000);
    
    // Store sidebar state in localStorage
    const sidebar = document.getElementById('sidebar');
    const sidebarState = localStorage.getItem('sidebarState');
    
    if (sidebarState === 'collapsed') {
        sidebar.classList.add('collapsed');
        document.querySelector('.main-content').classList.add('expanded');
    }
    
    // Handle sidebar collapse/expand
    document.addEventListener('sidebarToggle', function() {
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
    });
});
</script>