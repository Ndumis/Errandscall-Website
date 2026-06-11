<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title : 'ErrandsCall Portal'; ?></title>
  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../images/apple-touch-icon.png">
  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <!-- Custom Styles -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="<?php echo isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? 'dashboard-layout' : ''; ?>">
  <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
    <!-- Dashboard Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient shadow fixed-top" style="background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 50%, #ffd700 100%) !important;">
      <div class="container-fluid">
        <button class="navbar-toggler mr-2" type="button" id="sidebarToggle">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <a class="navbar-brand" href="dashboard.php">
          <img src="../images/logo.png" alt="ErrandsCall Logo">
          <span class="ml-2">Portal</span>
        </a>
        
        <div class="navbar-nav ml-auto">
          <!-- Notifications -->
          <div class="nav-item dropdown mr-3">
            <a class="nav-link dropdown-toggle text-white" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown">
              <i class="fas fa-bell"></i>
              <span class="badge badge-danger badge-pill" id="notificationCount">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown" style="min-width: 300px;">
              <h6 class="dropdown-header">Notifications</h6>
              <div id="notificationsList">
                <div class="text-center py-3">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  <span class="ml-2">Loading notifications...</span>
                </div>
              </div>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-center" href="#" id="markAllRead">
                <small>Mark all as read</small>
              </a>
            </div>
          </div>
          
          <!-- User Menu -->
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
              <i class="fas fa-user-circle mr-1"></i>
              <span class="d-none d-lg-inline"><?php echo $_SESSION['user_name']; ?></span>
              <span class="badge badge-light ml-1"><?php echo ucfirst($_SESSION['user_role']); ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
              <a class="dropdown-item" href="profile.php">
                <i class="fas fa-user mr-2"></i>My Profile
              </a>
              <a class="dropdown-item" href="settings.php">
                <i class="fas fa-cog mr-2"></i>Settings
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="php/logout.php">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
              </a>
            </div>
          </div>
        </div>
      </div>
    </nav>
  <?php else: ?>
    <!-- Simple Header for Authentication Pages -->
  <?php endif; ?>