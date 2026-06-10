<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Downloads | ErrandsCall - Important Documents & Forms</title>
  <meta name="description" content="Download important forms and documents for vehicle licensing services including consent forms, declaration letters, and application forms.">
  
  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <!-- AOS Library -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
  <!-- Custom Styles -->
  <link rel="stylesheet" href="css/style.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">

  <!-- Canonical -->
  <link rel="canonical" href="https://www.errandscall.co.za/downloads.php">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ErrandsCall">
  <meta property="og:title" content="Downloads | ErrandsCall - Important Documents & Forms">
  <meta property="og:description" content="Download important forms and documents for vehicle licensing services including consent forms, declaration letters, and application forms.">
  <meta property="og:url" content="https://www.errandscall.co.za/downloads.php">
  <meta property="og:image" content="https://www.errandscall.co.za/images/logo.png">
  <meta property="og:locale" content="en_ZA">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Downloads | ErrandsCall - Important Documents & Forms">
  <meta name="twitter:description" content="Download important forms and documents for vehicle licensing services including consent forms, declaration letters, and application forms.">
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
      <h1 class="font-weight-bold display-4 mb-3">Downloads</h1>
      <p class="lead mb-4">Important documents and forms available for download</p>
      
      <!-- Search Bar -->
      <div class="search-bar mx-auto position-relative z-index-3">
        <input type="text" id="downloadSearch" class="form-control shadow" placeholder="🔍 Search documents...">
        <div class="search-results mt-2"></div>
      </div>
    </div>
    
    <!-- Background Pattern -->
    <div class="position-absolute top-0 start-0 w-100 h-100 z-index-1 pattern-overlay"></div>
  </section>

  <!-- Download Categories -->
  <section class="py-4 bg-light">
    <div class="container">
      <div class="text-center mb-4">
        <h3 class="text-gradient" data-aos="fade-up">Browse by Category</h3>
      </div>
      <div class="row justify-content-center" data-aos="fade-up">
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter active" data-category="all">All Documents</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="forms">Application Forms</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="legal">Legal Documents</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="licensing">Licensing</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="permits">Permits & Certificates</button>
        </div>
      </div>
    </div>
  </section>

  <!-- Downloads List -->
  <section class="py-5">
    <div class="container">
      <div class="row" id="downloadsList">

        <!-- 1. Consent Form -->
        <div class="col-lg-6 mb-4 download-item" data-category="legal" data-aos="fade-up" data-aos-delay="100">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-file-signature fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">ErrandsCall Consent Form</h5>
                  <p class="card-text text-muted mb-3">Authorization form required for processing services on your behalf.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">Legal</span>
                    </div>
                    <small class="text-muted">125 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/consent-form.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/consent-form.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="consent-form.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Declaration Letter -->
        <div class="col-lg-6 mb-4 download-item" data-category="legal" data-aos="fade-up" data-aos-delay="150">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-file-contract fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">Declaration Letter</h5>
                  <p class="card-text text-muted mb-3">Used to declare specific details and information for various applications.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">Legal</span>
                    </div>
                    <small class="text-muted">98 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/declaration-letter.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/declaration-letter.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="declaration-letter.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Application for Licensing (ALV) -->
        <div class="col-lg-6 mb-4 download-item" data-category="forms" data-aos="fade-up" data-aos-delay="200">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-id-card fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">Application for Licensing of Motor Vehicle (ALV)</h5>
                  <p class="card-text text-muted mb-3">Official form for annual licensing and renewal of your motor vehicle.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">Form</span>
                    </div>
                    <small class="text-muted">210 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/application-alv.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/application-alv.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="application-alv.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Change of Address (NCP) -->
        <div class="col-lg-6 mb-4 download-item" data-category="forms" data-aos="fade-up" data-aos-delay="250">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-map-marker-alt fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">Notification of Change of Address (NCP)</h5>
                  <p class="card-text text-muted mb-3">Form to update your address details for vehicle and license records.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">Form</span>
                    </div>
                    <small class="text-muted">156 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/change-of-address-ncp.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/change-of-address-ncp.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="change-of-address-ncp.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 5. Temporary Permit (TSP1) -->
        <div class="col-lg-6 mb-4 download-item" data-category="permits" data-aos="fade-up" data-aos-delay="100">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-file-alt fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">Application for Temporary Permit (TSP1)</h5>
                  <p class="card-text text-muted mb-3">Apply for temporary or special permits for vehicle operations.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">Permit</span>
                    </div>
                    <small class="text-muted">189 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/temporary-permit-tsp1.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/temporary-permit-tsp1.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="temporary-permit-tsp1.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 6. Deregistration (ADV) -->
        <div class="col-lg-6 mb-4 download-item" data-category="forms" data-aos="fade-up" data-aos-delay="150">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-times-circle fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">Application for Deregistration (ADV)</h5>
                  <p class="card-text text-muted mb-3">Required for deregistration of scrapped, stolen, or exported vehicles.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">Form</span>
                    </div>
                    <small class="text-muted">234 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/deregistration-adv.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/deregistration-adv.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="deregistration-adv.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 7. Driving Licence (DL1) -->
        <div class="col-lg-6 mb-4 download-item" data-category="licensing" data-aos="fade-up" data-aos-delay="200">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-id-card-alt fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">Application for Driving Licence (DL1)</h5>
                  <p class="card-text text-muted mb-3">Form used for new driving licence applications and renewals.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">License</span>
                    </div>
                    <small class="text-muted">278 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/driving-licence-dl1.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/driving-licence-dl1.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="driving-licence-dl1.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- 8. Roadworthy Certificate Application -->
        <div class="col-lg-6 mb-4 download-item" data-category="permits" data-aos="fade-up" data-aos-delay="250">
          <div class="card download-card shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="download-icon mr-3">
                  <i class="fas fa-car-side fa-2x text-gradient"></i>
                </div>
                <div class="flex-grow-1">
                  <h5 class="card-title mb-2">Roadworthy Certificate Application</h5>
                  <p class="card-text text-muted mb-3">Application form for vehicle roadworthiness certification and testing.</p>
                  <div class="download-meta d-flex justify-content-between align-items-center">
                    <div>
                      <span class="badge badge-warning mr-2">PDF</span>
                      <span class="badge badge-light">Certificate</span>
                    </div>
                    <small class="text-muted">195 KB</small>
                  </div>
                </div>
              </div>
              <div class="download-actions mt-3 pt-3 border-top">
                <a href="downloads/roadworthy-certificate.pdf" target="_blank" class="btn btn-outline-primary btn-sm mr-2 preview-download">
                  <i class="fas fa-eye mr-1"></i> Preview
                </a>
                <a href="downloads/roadworthy-certificate.pdf" download class="btn btn-gradient btn-sm download-btn" data-file="roadworthy-certificate.pdf">
                  <i class="fas fa-download mr-1"></i> Download
                </a>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- row -->

      <!-- No Results Message -->
      <div id="noResults" class="text-center py-5" style="display: none;">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <h4 class="text-muted">No documents found</h4>
        <p>Try adjusting your search or filter criteria</p>
        <button class="btn btn-primary reset-filters">Reset Filters</button>
      </div>

      <!-- Download Statistics -->
      <div class="download-stats mt-5 p-4 rounded bg-light text-center" data-aos="fade-up">
        <h4 class="text-gradient mb-3">Download Statistics</h4>
        <div class="row">
          <div class="col-md-3 col-6 mb-3">
            <div class="stat-item">
              <h3 class="text-gradient counter" data-count="1254">0</h3>
              <p>Total Downloads</p>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="stat-item">
              <h3 class="text-gradient counter" data-count="8">0</h3>
              <p>Available Documents</p>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="stat-item">
              <h3 class="text-gradient counter" data-count="356">0</h3>
              <p>This Month</p>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-3">
            <div class="stat-item">
              <h3 class="text-gradient counter" data-count="98">0</h3>
              <p>This Week</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- Help Section -->
  <section class="py-5 bg-light">
    <div class="container text-center" data-aos="fade-up">
      <h3 class="text-gradient mb-4">Need Help With Documents?</h3>
      <p class="lead mb-4">Our team can assist you with filling out forms and understanding requirements</p>
      <a href="contact.php" class="btn btn-primary btn-lg mr-3">Get Assistance</a>
      <a href="faq.php" class="btn btn-outline-primary btn-lg">Visit FAQ</a>
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
      // Download search functionality
      $("#downloadSearch").on("keyup", function(){
        let value = $(this).val().toLowerCase();
        let category = $('.category-filter.active').data('category');
        
        filterDownloads(value, category);
      });
      
      // Category filter functionality
      $(".category-filter").click(function(){
        $('.category-filter').removeClass('active');
        $(this).addClass('active');
        
        let category = $(this).data('category');
        let searchValue = $('#downloadSearch').val().toLowerCase();
        
        filterDownloads(searchValue, category);
      });
      
      // Reset filters
      $(".reset-filters").click(function(){
        $('#downloadSearch').val('');
        $('.category-filter').removeClass('active');
        $('[data-category="all"]').addClass('active');
        filterDownloads('', 'all');
      });
      
      // Filter downloads function
      function filterDownloads(searchValue, category) {
        let visibleItems = 0;
        
        $("#downloadsList .download-item").each(function(){
          let downloadText = $(this).text().toLowerCase();
          let downloadCategory = $(this).data('category');
          
          let matchesSearch = searchValue === '' || downloadText.indexOf(searchValue) > -1;
          let matchesCategory = category === 'all' || downloadCategory === category;
          
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
      
      // Download tracking
      $(".download-btn").click(function(){
        let fileName = $(this).data('file');
        
        // Simulate download tracking (in real implementation, this would send to server)
        console.log('Download started:', fileName);
        
        // Show download confirmation
        let downloadCard = $(this).closest('.download-card');
        downloadCard.addClass('download-highlight');
        
        setTimeout(function() {
          downloadCard.removeClass('download-highlight');
        }, 2000);
      });
      
      // Preview tracking
      $(".preview-download").click(function(){
        let fileName = $(this).attr('href');
        console.log('Preview opened:', fileName);
      });
      
      // Counter animation for stats
      $('.counter').each(function() {
        $(this).prop('Counter', 0).animate({
          Counter: $(this).data('count')
        }, {
          duration: 2000,
          easing: 'swing',
          step: function(now) {
            $(this).text(Math.ceil(now));
          }
        });
      });
    });
  </script>
</body>
</html>