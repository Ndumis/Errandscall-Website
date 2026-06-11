// Escape HTML special characters before inserting user-controlled text into
// the DOM. Safe for both element text content and quoted attribute values.
function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Add to your main.js or dashboard.js
function checkForNewMessages() {
    fetch('php/get-unread-counts.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update sidebar badge
                const badge = document.getElementById('unreadMessagesCount');
                if (badge && data.unreadMessages > 0) {
                    badge.textContent = data.unreadMessages;
                    badge.style.display = 'inline';
                    
                    // Show desktop notification if permitted
                    if (data.unreadMessages > 0 && 'Notification' in window) {
                        if (Notification.permission === 'granted') {
                            new Notification('New Chat Message', {
                                body: `You have ${data.unreadMessages} unread message(s)`,
                                icon: '/assets/logo.png'
                            });
                        }
                    }
                }
            }
        })
        .catch(error => console.error('Error checking messages:', error));
}

// Check for new messages every 30 seconds
setInterval(checkForNewMessages, 30000);

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}