<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Errands Call - Professional Vehicle Licensing Services</title>
  <meta name="description" content="ErrandsCall provides professional vehicle licensing services including license disc renewal, change of ownership, and fine payments.">
  
  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <!-- AOS Library for animations -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <!-- Your Custom Styles -->
  <link rel="stylesheet" href="css/style.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">

  <!-- Canonical -->
  <link rel="canonical" href="https://www.errandscall.co.za/">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ErrandsCall">
  <meta property="og:title" content="ErrandsCall - Professional Vehicle Licensing Services">
  <meta property="og:description" content="ErrandsCall provides professional vehicle licensing services including license disc renewal, change of ownership, and fine payments.">
  <meta property="og:url" content="https://www.errandscall.co.za/">
  <meta property="og:image" content="https://www.errandscall.co.za/images/logo.png">
  <meta property="og:locale" content="en_ZA">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="ErrandsCall - Professional Vehicle Licensing Services">
  <meta name="twitter:description" content="ErrandsCall provides professional vehicle licensing services including license disc renewal, change of ownership, and fine payments.">
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

  <!-- Hero Carousel -->
  <div id="heroCarousel" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
      <li data-target="#heroCarousel" data-slide-to="0" class="active"></li>
      <li data-target="#heroCarousel" data-slide-to="1"></li>
      <li data-target="#heroCarousel" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner">
      <!-- Slide 1 -->
      <div class="carousel-item active" style="background-image: url('images/hero/slide1.jpg');">
        <div class="carousel-content">
          <div class="carousel-caption">
            <span class="hero-eyebrow">Cape Town's Trusted Licensing Partner</span>
            <h1>We Handle Your Licensing Needs</h1>
            <p>Fast, reliable, and professional service at your fingertips — so you can stay on the couch while we do the queuing.</p>
            <ul class="hero-features">
              <li><i class="fas fa-check-circle"></i>Same-Day Service</li>
              <li><i class="fas fa-check-circle"></i>Free Pickup &amp; Delivery</li>
              <li><i class="fas fa-check-circle"></i>5,000+ Happy Clients</li>
            </ul>
            <a href="services.php" class="btn btn-primary mt-2">Explore Services</a>
          </div>
        </div>
      </div>
      <!-- Slide 2 -->
      <div class="carousel-item" style="background-image: url('images/hero/slide2.jpg');">
        <div class="carousel-content">
          <div class="carousel-caption">
            <span class="hero-eyebrow">License Disc Renewal</span>
            <h2>Never Miss a Renewal Again</h2>
            <p>Quick and convenient license renewals — no queues, no stress, handled from start to finish on your behalf.</p>
            <ul class="hero-features">
              <li><i class="fas fa-check-circle"></i>24-48 Hour Turnaround</li>
              <li><i class="fas fa-check-circle"></i>Reminder Service</li>
            </ul>
            <a href="services.php" class="btn btn-primary mt-2">Renew My License</a>
          </div>
        </div>
      </div>
      <!-- Slide 3 -->
      <div class="carousel-item" style="background-image: url('images/hero/slide3.jpg');">
        <div class="carousel-content">
          <div class="carousel-caption">
            <span class="hero-eyebrow">Change of Ownership</span>
            <h2>Simplify Your Vehicle Transfer</h2>
            <p>We simplify the ownership transfer process with minimal paperwork and expert guidance every step of the way.</p>
            <ul class="hero-features">
              <li><i class="fas fa-check-circle"></i>Minimal Paperwork</li>
              <li><i class="fas fa-check-circle"></i>Expert Guidance</li>
            </ul>
            <a href="contact.php" class="btn btn-primary mt-2">Get Started</a>
          </div>
        </div>
      </div>
    </div>
    <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </a>
    <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
      <span class="carousel-control-next-icon"></span>
    </a>
  </div>

  <!-- Stats Section -->
  <section class="stats-section py-4 bg-light">
    <div class="container">
      <div class="row text-center">
        <div class="col-md-3 col-6 mb-3" data-aos="fade-up" data-aos-delay="100">
          <div class="stat-item">
            <h3 class="text-gradient counter" data-count="5000">0</h3>
            <p>Happy Clients</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-3" data-aos="fade-up" data-aos-delay="200">
          <div class="stat-item">
            <h3 class="text-gradient counter" data-count="15000">0</h3>
            <p>Services Rendered</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-3" data-aos="fade-up" data-aos-delay="300">
          <div class="stat-item">
            <h3 class="text-gradient counter" data-count="5">0</h3>
            <p>Years Experience</p>
          </div>
        </div>
        <div class="col-md-3 col-6 mb-3" data-aos="fade-up" data-aos-delay="400">
          <div class="stat-item">
            <h3 class="text-gradient counter" data-count="24">0</h3>
            <p>Hours Support</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About -->
  <section class="text-center py-5" data-aos="fade-up">
    <div class="container">
      <h2>About Us</h2>
      <p class="lead">ErrandsCall is a professional vehicle licensing errands service provider. We assist individuals and businesses with renewing license discs, handling change of ownership, and more.</p>
      <a href="about.php" class="btn btn-outline-primary mt-3">Read More</a>
    </div>
  </section>

  <!-- Services -->
  <section class="bg-light py-5" data-aos="fade-up">
    <div class="container">
      <h2 class="text-center mb-5">Our Services</h2>
      <div class="row">
        <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="100">
          <div class="card service-card h-100 p-3 text-center">
            <div class="card-icon mb-3">
              <i class="fas fa-id-card fa-2x text-gradient"></i>
            </div>
            <h3>License Disc Renewal</h3>
            <p>We renew your license discs quickly and conveniently.</p>
            <button class="btn btn-gradient mt-auto service-toggle">Learn More</button>
            <div class="service-info">
              <p>Our license disc renewal service ensures your vehicle remains legally compliant without the hassle of queues and paperwork.</p>
              <ul class="text-left">
                <li>Fast processing within 24-48 hours</li>
                <li>Collection and delivery options</li>
                <li>Reminder service for next renewal</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="200">
          <div class="card service-card h-100 p-3 text-center">
            <div class="card-icon mb-3">
              <i class="fas fa-exchange-alt fa-2x text-gradient"></i>
            </div>
            <h3>Change of Ownership</h3>
            <p>We help process ownership transfers with minimal paperwork.</p>
            <button class="btn btn-gradient mt-auto service-toggle">Learn More</button>
            <div class="service-info">
              <p>Simplify the vehicle ownership transfer process with our expert assistance and documentation handling.</p>
              <ul class="text-left">
                <li>Complete documentation handling</li>
                <li>Expert guidance through the process</li>
                <li>Fast turnaround time</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="zoom-in" data-aos-delay="300">
          <div class="card service-card h-100 p-3 text-center">
            <div class="card-icon mb-3">
              <i class="fas fa-file-invoice-dollar fa-2x text-gradient"></i>
            </div>
            <h3>Fine Payments</h3>
            <p>Settle your outstanding fines efficiently and avoid penalties.</p>
            <button class="btn btn-gradient mt-auto service-toggle">Learn More</button>
            <div class="service-info">
              <p>We handle your traffic fine payments promptly to prevent penalties and legal issues.</p>
              <ul class="text-left">
                <li>Prompt payment to avoid penalties</li>
                <li>Dispute assistance if needed</li>
                <li>Payment tracking and confirmation</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center mt-4">
        <a href="services.php" class="btn btn-primary">See All Services</a>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="py-5" data-aos="fade-up">
    <div class="container">
      <h2 class="text-center mb-5">What Our Clients Say</h2>
      <div class="row">
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
          <div class="card testimonial-card h-100">
            <div class="card-body text-center">
              <div class="testimonial-rating mb-3">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
              </div>
              <p class="testimonial-text">"ErrandsCall saved me so much time and hassle. My license disc was renewed in record time!"</p>
              <div class="testimonial-author">
                <strong>John D.</strong>
                <p class="text-muted small">Pretoria</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
          <div class="card testimonial-card h-100">
            <div class="card-body text-center">
              <div class="testimonial-rating mb-3">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
              </div>
              <p class="testimonial-text">"The change of ownership service was seamless. Highly recommended for anyone buying or selling a vehicle."</p>
              <div class="testimonial-author">
                <strong>Sarah M.</strong>
                <p class="text-muted small">Johannesburg</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
          <div class="card testimonial-card h-100">
            <div class="card-body text-center">
              <div class="testimonial-rating mb-3">
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
                <i class="fas fa-star text-warning"></i>
              </div>
              <p class="testimonial-text">"Outstanding service! They handled all my fine payments and kept me updated throughout the process."</p>
              <div class="testimonial-author">
                <strong>Michael T.</strong>
                <p class="text-muted small">Cape Town</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Banner -->
  <section class="cta-banner text-center text-white py-5" data-aos="fade-up">
    <div class="container">
      <h2>Ready to Simplify Your Vehicle Licensing?</h2>
      <p class="lead mb-4">Join thousands of satisfied clients who trust us with their licensing needs.</p>
      <a href="contact.php" class="btn btn-light btn-lg mr-3">Get Started</a>
      <a href="tel:+27789444633" class="btn btn-outline-light btn-lg">Call Us Now</a>
    </div>
  </section>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>
  
  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="js/main.js"></script>
  
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
    
    // Counter animation for stats
    $(document).ready(function() {
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
      
      // Service card toggle
      $('.service-toggle').click(function() {
        $(this).siblings('.service-info').slideToggle();
        $(this).text(function(i, text) {
          return text === 'Learn More' ? 'Show Less' : 'Learn More';
        });
      });
    });
  </script>
</body>
</html>