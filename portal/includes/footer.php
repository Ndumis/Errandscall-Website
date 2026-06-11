  <!-- Footer -->
  <footer class="bg-dark text-white mt-5 py-4">
    <div class="container text-center">
      <p>&copy; <?php echo date('Y'); ?> ErrandsCall. All rights reserved.</p>
    </div>
  </footer>

  <?php $isTrackedRole = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && in_array($_SESSION['user_role'], ['worker', 'manager']); ?>
  <?php if ($isTrackedRole): ?>
  <!-- Location Sharing Required Modal -->
  <div class="modal fade" id="locationPermissionModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background: var(--primary-gradient); color: #fff;">
          <h5 class="modal-title"><i class="fas fa-map-marker-alt mr-2"></i>Location Sharing Required</h5>
        </div>
        <div class="modal-body">
          <div id="locationPromptDefault">
            <p>ErrandsCall requires live location sharing for worker and manager accounts so admins/managers can see who's on the road and who's at the office.</p>
            <p class="text-muted small mb-0">Only your current position is shared in real time; it is not stored as a history.</p>
          </div>
          <div id="locationPromptDenied" class="d-none">
            <div class="alert alert-warning mb-3">
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Location access is currently blocked for this site.
            </div>
            <p class="mb-1">To continue, please enable location for this site:</p>
            <ol class="small mb-0 pl-4">
              <li>Click the location/lock icon in your browser's address bar</li>
              <li>Set "Location" to "Allow"</li>
              <li>Reload this page</li>
            </ol>
          </div>
          <div id="locationPromptInsecure" class="d-none">
            <div class="alert alert-warning mb-3">
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Location sharing requires a secure connection.
            </div>
            <p class="mb-0 small">Browsers only allow location access over <strong>https://</strong> (or on <strong>localhost</strong>). Please open this portal using its secure (https) address, then reload this page.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-gradient btn-block" id="locationEnableBtn">
            <i class="fas fa-location-arrow mr-1"></i>Enable Location Sharing
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/sidebar.js"></script>
  <script src="js/main.js"></script>
  <?php if ($isTrackedRole): ?>
  <script>
    const currentUserRole = '<?php echo $_SESSION['user_role']; ?>';
    const currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;
  </script>
  <script src="js/location-tracker.js"></script>
  <?php endif; ?>
</body>
</html>