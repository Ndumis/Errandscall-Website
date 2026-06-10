<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Privacy Policy | ErrandsCall - Professional Vehicle Licensing Services</title>
  <meta name="description" content="ErrandsCall's privacy policy explaining how we collect, use, and protect your personal information in line with South Africa's POPIA.">

  <!-- Bootstrap CSS -->
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
  <link rel="canonical" href="https://www.errandscall.co.za/privacy.php">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ErrandsCall">
  <meta property="og:title" content="Privacy Policy | ErrandsCall - Professional Vehicle Licensing Services">
  <meta property="og:description" content="ErrandsCall's privacy policy explaining how we collect, use, and protect your personal information in line with South Africa's POPIA.">
  <meta property="og:url" content="https://www.errandscall.co.za/privacy.php">
  <meta property="og:image" content="https://www.errandscall.co.za/images/logo.png">
  <meta property="og:locale" content="en_ZA">

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Privacy Policy | ErrandsCall - Professional Vehicle Licensing Services">
  <meta name="twitter:description" content="ErrandsCall's privacy policy explaining how we collect, use, and protect your personal information in line with South Africa's POPIA.">
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

  <!-- Page Header -->
  <section class="page-header text-center text-white d-flex align-items-center position-relative overflow-hidden"
           style="background: linear-gradient(rgba(0,0,0,0.15), rgba(0,0,0,0.15)), linear-gradient(135deg, #ff8c00 0%, #ffd700 100%); height: 280px;">
    <div class="container position-relative z-index-3" data-aos="fade-up">
      <h1 class="font-weight-bold display-4 mb-3">Privacy Policy</h1>
      <p class="lead mb-0">How ErrandsCall collects, uses, and protects your information</p>
    </div>
  </section>

  <!-- Content -->
  <section class="content-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9">

          <p class="text-muted">Last updated: <?php echo date('d F Y'); ?></p>

          <p>
            ErrandsCall ("we", "us", "our") provides vehicle licensing and registration errand
            services. This policy explains what personal information we collect, why we collect
            it, and how it is used and protected, in line with South Africa's
            Protection of Personal Information Act (POPIA).
          </p>

          <h4 class="mt-5 mb-3">1. Information We Collect</h4>
          <p>When you use our website, contact form, or services, we may collect:</p>
          <ul>
            <li>Your name, email address, and phone number</li>
            <li>Details you provide in the contact form (subject, message, service of interest)</li>
            <li>Identity and vehicle documents you submit to us when processing a licensing service</li>
          </ul>

          <h4 class="mt-5 mb-3">2. How We Use Your Information</h4>
          <ul>
            <li>To respond to your enquiries and provide requested services</li>
            <li>To process vehicle licensing, registration, and related government applications on your behalf</li>
            <li>To send you updates about the status of your request</li>
          </ul>

          <h4 class="mt-5 mb-3">3. Sharing of Information</h4>
          <p>
            We do not sell your personal information. We only share information with relevant
            government departments (e.g. Department of Transport / licensing authorities) where
            necessary to complete the service you have requested.
          </p>

          <h4 class="mt-5 mb-3">4. Data Security</h4>
          <p>
            We take reasonable technical and organisational measures to protect your personal
            information against loss, misuse, and unauthorised access.
          </p>

          <h4 class="mt-5 mb-3">5. Your Rights</h4>
          <p>
            Under POPIA, you have the right to access, correct, or request deletion of your
            personal information held by us. To exercise these rights, contact us using the
            details below.
          </p>

          <h4 class="mt-5 mb-3">6. Contact Us</h4>
          <p>
            For any privacy-related questions, contact us at
            <a href="mailto:info@errandscall.co.za">info@errandscall.co.za</a> or
            <a href="tel:+27789444633">+27 78 944 4633</a>.
          </p>

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
    AOS.init({ duration: 800, once: true, offset: 100 });
    $(window).on('load', function() {
      $('#loading').fadeOut('slow');
    });
  </script>
</body>
</html>
