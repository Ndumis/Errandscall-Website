<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Services | ErrandsCall - Professional Vehicle Licensing Services</title>
  <meta name="description" content="Comprehensive vehicle licensing services including license renewals, change of ownership, driver's license bookings, and more. Save time and avoid queues.">
  
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <!-- AOS Library -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="css/style.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">

  <!-- Canonical -->
  <link rel="canonical" href="https://www.errandscall.co.za/services.php">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ErrandsCall">
  <meta property="og:title" content="Services | ErrandsCall - Professional Vehicle Licensing Services">
  <meta property="og:description" content="Comprehensive vehicle licensing services including license renewals, change of ownership, driver's license bookings, and more. Save time and avoid queues.">
  <meta property="og:url" content="https://www.errandscall.co.za/services.php">
  <meta property="og:image" content="https://www.errandscall.co.za/images/logo.png">
  <meta property="og:locale" content="en_ZA">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Services | ErrandsCall - Professional Vehicle Licensing Services">
  <meta name="twitter:description" content="Comprehensive vehicle licensing services including license renewals, change of ownership, driver's license bookings, and more. Save time and avoid queues.">
  <meta name="twitter:image" content="https://www.errandscall.co.za/images/logo.png">
</head>
<body>
  <!-- Loading Spinner -->
  <div id="loading" class="loading-spinner">
    <div class="spinner-border text-warning" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>

  <?php include 'includes/header.php'; ?>

    <!-- Enhanced Page Header -->
  <section class="page-header text-center text-white d-flex align-items-center position-relative overflow-hidden" 
           style="background: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), linear-gradient(135deg, #ff8c00 0%, #ffd700 100%); height: 300px;">
    <div class="container position-relative z-index-3" data-aos="fade-up">
      <h1 class="font-weight-bold display-4 mb-3">Our Services</h1>
      <p class="lead mb-4">We simplify your life by taking away the time you would spend waiting at the Traffic Department.</p>
      
      <!-- Search Bar -->
      <div class="search-bar mx-auto position-relative z-index-3">
        <input type="text" id="serviceSearch" class="form-control shadow" placeholder="🔍 Search services...">
        <div class="search-results mt-2"></div>
      </div>
    </div>
    
    <!-- Background Pattern -->
    <div class="position-absolute top-0 start-0 w-100 h-100 z-index-1 pattern-overlay"></div>
  </section>

  <!-- Service Categories Filter -->
  <section class="py-4 bg-light">
    <div class="container">
      <div class="text-center mb-4">
        <h3 class="text-gradient" data-aos="fade-up">Browse by Category</h3>
      </div>
      <div class="row justify-content-center" data-aos="fade-up">
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter active" data-category="all">All Services</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="licensing">Licensing</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="registration">Registration</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="documentation">Documentation</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="special">Special Services</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Grid -->
  <section class="py-5">
    <div class="container">
      <div class="row" id="servicesList">

        <!-- 1. Vehicle License Renewal -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="licensing" data-aos="fade-up" data-aos-delay="100">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-id-card-alt fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Vehicle License Renewal</h5>
              <p class="text-muted">Annual license disc renewals made easy – no queues, no penalties, just convenience.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">2-3 days</span>
                <span class="badge badge-light ml-1">Popular</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-renewal">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-renewal">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>Copy of ID (must be clear)</li>
                <li>Renewal reminder or copy of license disc</li>
                <li>Proof of address</li>
              </ul>
              <p><strong>Duration:</strong> 2–3 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Online Drivers License Renewal Booking -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="licensing" data-aos="fade-up" data-aos-delay="150">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-calendar-check fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Online Driver's License Renewal Booking</h5>
              <p class="text-muted">We book and manage your driver's license renewal appointments online.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">Appointment</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-driver">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-driver">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID Copy</li>
                <li>Current driver's license</li>
                <li>Proof of address</li>
              </ul>
              <p><strong>Duration:</strong> Appointment availability dependent</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Business Vehicle Registration -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="registration" data-aos="fade-up" data-aos-delay="200">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-building fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Business Vehicle Registration</h5>
              <p class="text-muted">We register vehicles for companies, ensuring smooth fleet operations.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">3-5 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-business">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-business">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>Company documents (CK/BRN)</li>
                <li>Proof of address</li>
                <li>Vehicle documents</li>
              </ul>
              <p><strong>Duration:</strong> 3–5 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Vehicle De-registration -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="registration" data-aos="fade-up" data-aos-delay="100">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-times-circle fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Vehicle De-registration</h5>
              <p class="text-muted">We handle vehicle de-registrations in cases of sale, theft, or scrapping.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">2-4 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-dereg">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-dereg">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID copy</li>
                <li>Original registration papers</li>
                <li>Police clearance (if stolen)</li>
              </ul>
              <p><strong>Duration:</strong> 2–4 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. Change of Ownership -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="registration" data-aos="fade-up" data-aos-delay="150">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-exchange-alt fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Change of Ownership</h5>
              <p class="text-muted">We assist in transferring vehicle ownership with ease.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">3-5 days</span>
                <span class="badge badge-light ml-1">Popular</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-ownership">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-ownership">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>Seller and Buyer IDs</li>
                <li>Registration papers</li>
                <li>Proof of address</li>
              </ul>
              <p><strong>Duration:</strong> 3–5 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 6. Roadworthy -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="documentation" data-aos="fade-up" data-aos-delay="200">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-car-side fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Roadworthy Certificates</h5>
              <p class="text-muted">We book and facilitate roadworthy tests for your vehicles.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">Appointment</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-roadworthy">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-roadworthy">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID Copy</li>
                <li>Vehicle registration papers</li>
              </ul>
              <p><strong>Duration:</strong> Appointment dependent</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 7. Police Clearance -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="documentation" data-aos="fade-up" data-aos-delay="100">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-shield-alt fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Police Clearance</h5>
              <p class="text-muted">We arrange police clearances for vehicles requiring verification.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">3-7 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-police">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-police">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID copy</li>
                <li>Vehicle papers</li>
                <li>Affidavit (if applicable)</li>
              </ul>
              <p><strong>Duration:</strong> 3–7 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 8. Personalised Number Plates -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="special" data-aos="fade-up" data-aos-delay="150">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-tags fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Personalised Number Plates</h5>
              <p class="text-muted">Custom number plates made to your request, registered and compliant.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">7-10 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-plates">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-plates">
              <p>Choose from alphanumeric or special plate options.</p>
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID copy</li>
                <li>Vehicle registration/license disc</li>
                <li>Proof of address</li>
                <li>3 preferred number options</li>
              </ul>
              <p><strong>Duration:</strong> 7–10 business days</p>
              <div class="service-note">
                <strong>Note:</strong> Due to demand, we can't guarantee your first choice.
              </div>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 9. Change of Province -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="registration" data-aos="fade-up" data-aos-delay="200">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-map-marker-alt fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Change of Province</h5>
              <p class="text-muted">We assist with moving your vehicle registration to another province.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">5-7 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-province">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-province">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID copy</li>
                <li>Proof of new residence</li>
                <li>Vehicle registration papers</li>
              </ul>
              <p><strong>Duration:</strong> 5–7 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 10. Number Plate Manufacturing -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="special" data-aos="fade-up" data-aos-delay="100">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-industry fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Number Plate Manufacturing</h5>
              <p class="text-muted">We manufacture new legal number plates for any registered vehicle.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">2 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-plate-man">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-plate-man">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID copy</li>
                <li>Registration papers</li>
              </ul>
              <p><strong>Duration:</strong> 2 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 11. Vehicle Sold / Trade In -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="registration" data-aos="fade-up" data-aos-delay="150">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-handshake fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Vehicle Sold / Trade In</h5>
              <p class="text-muted">We manage notifications of vehicles sold or traded in.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">3-5 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-tradein">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-tradein">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>ID copy</li>
                <li>Sale agreement</li>
                <li>Vehicle papers</li>
              </ul>
              <p><strong>Duration:</strong> 3–5 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 12. Change of Title Holder -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="registration" data-aos="fade-up" data-aos-delay="200">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-user-edit fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Change of Title Holder</h5>
              <p class="text-muted">We assist in updating vehicle title holder details.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">3-5 days</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-title">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-title">
              <h6 class="font-weight-bold text-gradient">Documents Required:</h6>
              <ul>
                <li>Current and new title holder IDs</li>
                <li>Proof of address</li>
                <li>Vehicle papers</li>
              </ul>
              <p><strong>Duration:</strong> 3–5 business days</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Request This Service</a>
              </div>
            </div>
          </div>
        </div>

        <!-- 13. Other Services -->
        <div class="col-lg-4 col-md-6 mb-4 service-item" data-category="special" data-aos="fade-up" data-aos-delay="100">
          <div class="card service-card h-100 shadow-sm">
            <div class="card-icon text-center pt-4">
              <i class="fas fa-cogs fa-3x text-gradient"></i>
            </div>
            <div class="card-body text-center">
              <h5 class="card-title font-weight-bold">Other Services</h5>
              <p class="text-muted">Contact us for any additional services or unique requests not listed here.</p>
              <div class="service-meta mb-3">
                <span class="badge badge-warning">Varies</span>
              </div>
              <button class="btn btn-gradient toggle-info" data-target="#info-other">
                More Info <i class="fas fa-chevron-down ml-1"></i>
              </button>
            </div>
            <div class="service-info" id="info-other">
              <p>We are here to assist with tailored solutions for vehicle administration needs.</p>
              <div class="text-center mt-3">
                <a href="contact.php" class="btn btn-primary btn-sm">Contact Us</a>
              </div>
            </div>
          </div>
        </div>

      </div>
      
      <!-- No Results Message -->
      <div id="noResults" class="text-center py-5" style="display: none;">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No services found</h4>
        <p>Try adjusting your search or filter criteria</p>
        <button class="btn btn-primary reset-filters">Reset Filters</button>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta-banner text-center text-white py-5">
    <div class="container" data-aos="fade-up">
      <h2 class="mb-4">Need Help With Vehicle Licensing?</h2>
      <p class="lead mb-4">Our team is ready to assist you with any licensing or registration needs.</p>
      <a href="contact.php" class="btn btn-light btn-lg mr-3">Contact Us</a>
      <a href="tel:+27789444633" class="btn btn-outline-light btn-lg">Call Now</a>
    </div>
  </section>

  <?php include 'includes/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  
  <script>
    // Initialize AOS
    AOS.init({ 
      duration: 800, 
      once: true,
      offset: 100 
    });
    
    // Remove loading spinner when page is loaded
    $(window).on('load', function() {
      $('#loading').fadeOut('slow');
    });
    
    $(document).ready(function(){
      // Toggle service info with animation
      $(".toggle-info").click(function(){
        let target = $(this).data("target");
        let icon = $(this).find('i');
        
        $(target).slideToggle(300, function() {
          if ($(this).is(':visible')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $(this).parent().addClass('expanded');
          } else {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $(this).parent().removeClass('expanded');
          }
        });
      });
      
      // Service search functionality
      $("#serviceSearch").on("keyup", function(){
        let value = $(this).val().toLowerCase();
        let category = $('.category-filter.active').data('category');
        
        filterServices(value, category);
      });
      
      // Category filter functionality
      $(".category-filter").click(function(){
        $('.category-filter').removeClass('active');
        $(this).addClass('active');
        
        let category = $(this).data('category');
        let searchValue = $('#serviceSearch').val().toLowerCase();
        
        filterServices(searchValue, category);
      });
      
      // Reset filters
      $(".reset-filters").click(function(){
        $('#serviceSearch').val('');
        $('.category-filter').removeClass('active');
        $('[data-category="all"]').addClass('active');
        filterServices('', 'all');
      });
      
      // Filter services function
      function filterServices(searchValue, category) {
        let visibleItems = 0;
        
        $("#servicesList .service-item").each(function(){
          let serviceText = $(this).text().toLowerCase();
          let serviceCategory = $(this).data('category');
          
          let matchesSearch = searchValue === '' || serviceText.indexOf(searchValue) > -1;
          let matchesCategory = category === 'all' || serviceCategory === category;
          
          if (matchesSearch && matchesCategory) {
            $(this).show();
            visibleItems++;
          } else {
            $(this).hide();
          }
        });
        
        // Show/hide no results message
        if (visibleItems === 0) {
          $('#noResults').show();
        } else {
          $('#noResults').hide();
        }
      }
    });
  </script>
</body>
</html>