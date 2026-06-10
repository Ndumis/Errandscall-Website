// Sidebar Toggle Functionality
$(document).ready(function() {
    // Wait a bit to ensure no conflicts with other scripts
    setTimeout(initSidebar, 100);
});

function initSidebar() {
    // Sidebar toggle for mobile
    $('#sidebarToggle').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
    });

    // Close sidebar when clicking outside on mobile
    $(document).off('click.sidebar').on('click.sidebar', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('#sidebar, #sidebarToggle').length && 
                $('#sidebar').hasClass('mobile-open')) {
                closeSidebar();
            }
        }
    });

    // Prevent sidebar close when clicking inside sidebar
    $('#sidebar').off('click').on('click', function(e) {
        e.stopPropagation();
    });

    // Handle window resize
    $(window).off('resize.sidebar').on('resize.sidebar', function() {
        if ($(window).width() > 768) {
            closeSidebar();
        }
    });

    // Enhanced dropdown handling for sidebar
    $('#sidebar .dropdown-toggle').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $this = $(this);
        const $submenu = $this.siblings('.collapse');
        const isCurrentlyOpen = $submenu.hasClass('show');
        
        // Close all other submenus first
        $('#sidebar .collapse').not($submenu).removeClass('show').slideUp(300);
        $('#sidebar .dropdown-toggle').not($this).removeClass('active');
        
        // Toggle current submenu
        if (!isCurrentlyOpen) {
            $submenu.addClass('show').slideDown(300);
            $this.addClass('active');
        } else {
            $submenu.removeClass('show').slideUp(300);
            $this.removeClass('active');
        }
    });

    // Initialize sidebar state
    if ($(window).width() <= 768) {
        $('#sidebar').removeClass('collapsed');
        $('.main-content').removeClass('expanded');
    }
}

// Toggle sidebar function
function toggleSidebar() {
    const $sidebar = $('#sidebar');
    const $mainContent = $('.main-content');
    
    if ($(window).width() <= 768) {
        // Mobile behavior
        $sidebar.toggleClass('mobile-open');
        $('body').toggleClass('sidebar-open');
        
        // Add overlay when sidebar is open
        if ($sidebar.hasClass('mobile-open')) {
            addOverlay();
        } else {
            removeOverlay();
        }
    } else {
        // Desktop behavior - toggle between collapsed and expanded
        $sidebar.toggleClass('collapsed');
        $mainContent.toggleClass('expanded');
    }
}

// Add overlay for mobile
function addOverlay() {
    if ($('#sidebarOverlay').length === 0) {
        $('body').append('<div id="sidebarOverlay" class="sidebar-overlay"></div>');
        
        $('#sidebarOverlay').on('click', function() {
            closeSidebar();
        });
    }
    $('#sidebarOverlay').addClass('active');
}

// Remove overlay
function removeOverlay() {
    const $overlay = $('#sidebarOverlay');
    if ($overlay.length) {
        $overlay.removeClass('active');
        setTimeout(() => {
            $overlay.remove();
        }, 300);
    }
}

// Close sidebar function
function closeSidebar() {
    $('#sidebar').removeClass('mobile-open');
    $('body').removeClass('sidebar-open');
    removeOverlay();
}