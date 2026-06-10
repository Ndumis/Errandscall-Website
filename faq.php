<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQ | ErrandsCall - Frequently Asked Questions</title>
  <meta name="description" content="Find answers to common questions about ErrandsCall vehicle licensing services, payments, delivery, and more.">
  
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
  <link rel="canonical" href="https://www.errandscall.co.za/faq.php">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ErrandsCall">
  <meta property="og:title" content="FAQ | ErrandsCall - Frequently Asked Questions">
  <meta property="og:description" content="Find answers to common questions about ErrandsCall vehicle licensing services, payments, delivery, and more.">
  <meta property="og:url" content="https://www.errandscall.co.za/faq.php">
  <meta property="og:image" content="https://www.errandscall.co.za/images/logo.png">
  <meta property="og:locale" content="en_ZA">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="FAQ | ErrandsCall - Frequently Asked Questions">
  <meta name="twitter:description" content="Find answers to common questions about ErrandsCall vehicle licensing services, payments, delivery, and more.">
  <meta name="twitter:image" content="https://www.errandscall.co.za/images/logo.png">

  <!-- FAQ structured data (rich results / AI answer engines) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "How do I make payment?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "We offer various convenient online payment options, including direct EFT transfers, credit/debit card payments, and secure online payment gateways. No refund can be processed on a license transaction once the license has been processed and paid for."
        }
      },
      {
        "@type": "Question",
        "name": "What happens if I don't pay the invoice on time?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "If payment is not received within 72 hours, the invoice will be automatically canceled. You'll need to submit a new application through your online profile to proceed with the service."
        }
      },
      {
        "@type": "Question",
        "name": "What if I have infringement notices against my name?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "If we detect infringement notices during processing, we'll contact you immediately, provide the total value of the fines, issue an additional invoice at your instruction, and pay them on your behalf once payment is received."
        }
      },
      {
        "@type": "Question",
        "name": "How will I know if my payment was received?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "We send an automated email confirmation immediately when payment is received, including payment confirmation, a transaction reference number, and the expected processing timeline."
        }
      },
      {
        "@type": "Question",
        "name": "How can I track my application status?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "We provide multiple ways to track your application: email updates, a courier tracking number once dispatched, real-time status in your customer portal, and optional SMS notifications."
        }
      },
      {
        "@type": "Question",
        "name": "When will my license disc be delivered?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Our standard timeline is 48 hours processing time (business days) plus 1-2 business days delivery, for a total of 3-4 business days."
        }
      },
      {
        "@type": "Question",
        "name": "Where do you deliver?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "We currently operate in Johannesburg, Pretoria, Midrand, Centurion, Krugersdorp, and other major Gauteng areas. We're currently only operating in Gauteng province and plan to expand to other provinces soon."
        }
      },
      {
        "@type": "Question",
        "name": "Can I renew multiple vehicle licenses?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes, we offer fleet services for multiple company vehicles, support adding trailers, motorbikes and caravans to your profile, bulk processing for multiple renewals, and corporate accounts for businesses."
        }
      },
      {
        "@type": "Question",
        "name": "What documents do I need for license renewal?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "For standard vehicle license renewal you'll need a clear copy of your ID document, your renewal notice or current license disc copy, proof of residential address not older than 3 months, and vehicle registration papers if available."
        }
      },
      {
        "@type": "Question",
        "name": "What are your operating hours?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Our customer service team is available Monday to Friday from 8:00 AM to 5:00 PM and Saturdays from 9:00 AM to 1:00 PM. We are closed on Sundays and public holidays. Online services are available 24/7 through your customer portal."
        }
      }
    ]
  }
  </script>
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
      <h1 class="font-weight-bold display-4 mb-3">Frequently Asked Questions</h1>
      <p class="lead mb-4">Find quick answers to common questions about our services</p>
      
      <!-- Search Bar -->
      <div class="search-bar mx-auto position-relative z-index-3">
        <input type="text" id="faqSearch" class="form-control shadow" placeholder="🔍 Search FAQs...">
        <div class="search-results mt-2"></div>
      </div>
    </div>
    
    <!-- Background Pattern -->
    <div class="position-absolute top-0 start-0 w-100 h-100 z-index-1 pattern-overlay"></div>
  </section>

  <!-- FAQ Categories -->
  <section class="py-4 bg-light">
    <div class="container">
      <div class="text-center mb-4">
        <h3 class="text-gradient" data-aos="fade-up">Browse by Category</h3>
      </div>
      <div class="row justify-content-center" data-aos="fade-up">
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter active" data-category="all">All FAQs</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="payments">Payments</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="delivery">Delivery</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="services">Services</button>
        </div>
        <div class="col-auto mb-2">
          <button class="btn btn-outline-primary category-filter" data-category="general">General</button>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Accordion -->
  <section class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div id="faqAccordion">
            
            <!-- Payments Category -->
            <h3 class="faq-category text-gradient mb-4" data-category="payments" data-aos="fade-up">
              <i class="fas fa-credit-card mr-2"></i> Payments & Billing
            </h3>

            <div class="card mb-3 faq-item" data-category="payments" data-aos="fade-up" data-aos-delay="100">
              <div class="card-header" id="heading1">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                    <span>How do I make payment?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse1" class="collapse show" aria-labelledby="heading1" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>We offer various convenient online payment options, including:</p>
                  <ul>
                    <li>Direct EFT transfers</li>
                    <li>Credit/Debit card payments</li>
                    <li>Secure online payment gateways</li>
                  </ul>
                  <p class="text-muted small mt-3">
                    <strong>Note:</strong> No refund can be processed on a license transaction once the license has been processed and paid for.
                  </p>
                </div>
              </div>
            </div>

            <div class="card mb-3 faq-item" data-category="payments" data-aos="fade-up" data-aos-delay="150">
              <div class="card-header" id="heading2">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                    <span>What happens if I don't pay the invoice on time?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>If payment is not received within 72 hours, the invoice will be automatically canceled. You'll need to submit a new application through your online profile to proceed with the service.</p>
                  <p class="text-muted small mt-3">
                    We recommend setting payment reminders to avoid service interruptions.
                  </p>
                </div>
              </div>
            </div>

            <div class="card mb-3 faq-item" data-category="payments" data-aos="fade-up" data-aos-delay="200">
              <div class="card-header" id="heading3">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                    <span>What if I have infringement notices against my name?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>If we detect infringement notices during processing:</p>
                  <ol>
                    <li>We'll contact you immediately to inform you about the outstanding fines</li>
                    <li>We'll provide the total value of infringement notices</li>
                    <li>At your instruction, we'll issue an additional invoice to cover these fines</li>
                    <li>We'll pay them on your behalf once payment is received</li>
                  </ol>
                </div>
              </div>
            </div>

            <div class="card mb-3 faq-item" data-category="payments" data-aos="fade-up" data-aos-delay="250">
              <div class="card-header" id="heading4">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                    <span>How will I know if my payment was received?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse4" class="collapse" aria-labelledby="heading4" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>We send automated email confirmation immediately when payment is received. This email includes:</p>
                  <ul>
                    <li>Payment confirmation</li>
                    <li>Transaction reference number</li>
                    <li>Expected processing timeline</li>
                  </ul>
                  <p class="text-muted small mt-3">
                    If you don't receive confirmation within 2 hours, please check your spam folder or contact our support team.
                  </p>
                </div>
              </div>
            </div>

            <!-- Delivery Category -->
            <h3 class="faq-category text-gradient mb-4 mt-5" data-category="delivery" data-aos="fade-up">
              <i class="fas fa-shipping-fast mr-2"></i> Delivery & Tracking
            </h3>

            <div class="card mb-3 faq-item" data-category="delivery" data-aos="fade-up" data-aos-delay="100">
              <div class="card-header" id="heading5">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                    <span>How can I track my application status?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse5" class="collapse" aria-labelledby="heading5" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>We provide multiple ways to track your application:</p>
                  <ul>
                    <li><strong>Email Updates:</strong> Regular status updates via email</li>
                    <li><strong>Tracking Number:</strong> Courier tracking number once dispatched</li>
                    <li><strong>Online Portal:</strong> Real-time status in your customer portal</li>
                    <li><strong>SMS Notifications:</strong> Optional SMS updates (opt-in required)</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card mb-3 faq-item" data-category="delivery" data-aos="fade-up" data-aos-delay="150">
              <div class="card-header" id="heading6">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                    <span>When will my license disc be delivered?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse6" class="collapse" aria-labelledby="heading6" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>Our standard processing and delivery timeline:</p>
                  <ul>
                    <li><strong>Processing Time:</strong> 48 hours (business days)</li>
                    <li><strong>Delivery Time:</strong> 1-2 business days after processing</li>
                    <li><strong>Total Timeline:</strong> 3-4 business days total</li>
                  </ul>
                  <p class="text-muted small mt-3">
                    We'll notify you immediately if there are any unexpected delays in processing.
                  </p>
                </div>
              </div>
            </div>

            <div class="card mb-3 faq-item" data-category="delivery" data-aos="fade-up" data-aos-delay="200">
              <div class="card-header" id="heading7">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                    <span>Where do you deliver?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse7" class="collapse" aria-labelledby="heading7" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>We currently operate in the following areas:</p>
                  <div class="row">
                    <div class="col-md-6">
                      <ul>
                        <li>Johannesburg & Surrounds</li>
                        <li>Pretoria & Surrounds</li>
                        <li>Midrand</li>
                      </ul>
                    </div>
                    <div class="col-md-6">
                      <ul>
                        <li>Centurion</li>
                        <li>Krugersdorp</li>
                        <li>All major Gauteng areas</li>
                      </ul>
                    </div>
                  </div>
                  <p class="text-muted small mt-3">
                    <strong>Note:</strong> We're currently only operating in Gauteng province. We plan to expand to other provinces soon.
                  </p>
                </div>
              </div>
            </div>

            <!-- Services Category -->
            <h3 class="faq-category text-gradient mb-4 mt-5" data-category="services" data-aos="fade-up">
              <i class="fas fa-concierge-bell mr-2"></i> Services
            </h3>

            <div class="card mb-3 faq-item" data-category="services" data-aos="fade-up" data-aos-delay="100">
              <div class="card-header" id="heading8">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                    <span>Can I renew multiple vehicle licenses?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse8" class="collapse" aria-labelledby="heading8" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>Yes! We offer comprehensive solutions for multiple vehicles:</p>
                  <ul>
                    <li><strong>Fleet Services:</strong> Manage multiple company vehicles</li>
                    <li><strong>Multiple Uploads:</strong> Add trailers, motorbikes, caravans to your profile</li>
                    <li><strong>Bulk Processing:</strong> Streamlined processing for multiple renewals</li>
                    <li><strong>Corporate Accounts:</strong> Specialized services for businesses</li>
                  </ul>
                  <p class="text-muted small mt-3">
                    Contact our corporate team for fleet management solutions and volume discounts.
                  </p>
                </div>
              </div>
            </div>

            <!-- General Category -->
            <h3 class="faq-category text-gradient mb-4 mt-5" data-category="general" data-aos="fade-up">
              <i class="fas fa-info-circle mr-2"></i> General Questions
            </h3>

            <div class="card mb-3 faq-item" data-category="general" data-aos="fade-up" data-aos-delay="100">
              <div class="card-header" id="heading9">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                    <span>What documents do I need for license renewal?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse9" class="collapse" aria-labelledby="heading9" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>For standard vehicle license renewal, you'll need:</p>
                  <ul>
                    <li>Clear copy of your ID document</li>
                    <li>Renewal notice or current license disc copy</li>
                    <li>Proof of residential address (not older than 3 months)</li>
                    <li>Vehicle registration papers (if available)</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="card mb-3 faq-item" data-category="general" data-aos="fade-up" data-aos-delay="150">
              <div class="card-header" id="heading10">
                <h5 class="mb-0">
                  <button class="btn btn-link text-left w-100 font-weight-bold d-flex justify-content-between align-items-center" 
                          data-toggle="collapse" data-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                    <span>What are your operating hours?</span>
                    <i class="fas fa-chevron-down transition-rotate"></i>
                  </button>
                </h5>
              </div>
              <div id="collapse10" class="collapse" aria-labelledby="heading10" data-parent="#faqAccordion">
                <div class="card-body">
                  <p>Our customer service team is available:</p>
                  <ul>
                    <li><strong>Weekdays:</strong> 8:00 AM - 5:00 PM</li>
                    <li><strong>Saturdays:</strong> 9:00 AM - 1:00 PM</li>
                    <li><strong>Sundays:</strong> Closed</li>
                    <li><strong>Public Holidays:</strong> Closed</li>
                  </ul>
                  <p class="text-muted small mt-3">
                    Online services are available 24/7 through your customer portal.
                  </p>
                </div>
              </div>
            </div>

          </div> <!-- #faqAccordion -->

          <!-- No Results Message -->
          <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No FAQs found</h4>
            <p>Try adjusting your search or filter criteria</p>
            <button class="btn btn-primary reset-filters">Reset Filters</button>
          </div>

          <!-- Contact CTA -->
          <div class="contact-cta mt-5 p-4 rounded text-center bg-light" data-aos="fade-up">
            <h4 class="text-gradient mb-3">Still have questions?</h4>
            <p class="mb-4">Our support team is here to help you with any additional questions.</p>
            <a href="contact.php" class="btn btn-primary mr-3">Contact Support</a>
            <a href="tel:+27789444633" class="btn btn-outline-primary">Call Now</a>
          </div>

        </div>
      </div>
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
      // FAQ search functionality
      $("#faqSearch").on("keyup", function(){
        let value = $(this).val().toLowerCase();
        let category = $('.category-filter.active').data('category');
        
        filterFAQs(value, category);
      });
      
      // Category filter functionality
      $(".category-filter").click(function(){
        $('.category-filter').removeClass('active');
        $(this).addClass('active');
        
        let category = $(this).data('category');
        let searchValue = $('#faqSearch').val().toLowerCase();
        
        filterFAQs(searchValue, category);
      });
      
      // Reset filters
      $(".reset-filters").click(function(){
        $('#faqSearch').val('');
        $('.category-filter').removeClass('active');
        $('[data-category="all"]').addClass('active');
        filterFAQs('', 'all');
      });
      
      // Filter FAQs function
      function filterFAQs(searchValue, category) {
        let visibleItems = 0;
        let visibleCategories = new Set();
        
        // Show/hide categories and items
        $(".faq-category").each(function(){
          let categoryType = $(this).data('category');
          let shouldShowCategory = category === 'all' || category === categoryType;
          
          if (shouldShowCategory) {
            $(this).show();
            visibleCategories.add(categoryType);
          } else {
            $(this).hide();
          }
        });
        
        $(".faq-item").each(function(){
          let faqText = $(this).text().toLowerCase();
          let faqCategory = $(this).data('category');
          
          let matchesSearch = searchValue === '' || faqText.indexOf(searchValue) > -1;
          let matchesCategory = category === 'all' || faqCategory === category;
          let categoryVisible = visibleCategories.has(faqCategory);
          
          if (matchesSearch && matchesCategory && categoryVisible) {
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
      
      // Accordion icon rotation
      $('.collapse').on('show.bs.collapse', function () {
        $(this).prev().find('.transition-rotate').css('transform', 'rotate(180deg)');
      });
      
      $('.collapse').on('hide.bs.collapse', function () {
        $(this).prev().find('.transition-rotate').css('transform', 'rotate(0deg)');
      });
    });
  </script>
</body>
</html>