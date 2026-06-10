
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<a href="https://api.whatsapp.com/send?phone=+27789444633&text=Hi- Please can you assist me?" class="floatWhatsapp" target="_blank"><i class="fab fa-whatsapp my-float"></i></a>
<!-- header.php -->
<!-- Local business structured data (SEO / local search / AI answer engines) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "ErrandsCall",
  "image": "https://www.errandscall.co.za/images/logo.png",
  "url": "https://www.errandscall.co.za/",
  "telephone": "+27789444633",
  "email": "info@errandscall.co.za",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "123 Licensing Street",
    "addressLocality": "Cape Town",
    "postalCode": "8001",
    "addressCountry": "ZA"
  },
  "areaServed": [
    "Cape Town", "Johannesburg", "Pretoria", "Gauteng", "Durban", "Northern Cape"
  ],
  "openingHoursSpecification": [
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
      "opens": "08:00",
      "closes": "17:00"
    },
    {
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": "Saturday",
      "opens": "09:00",
      "closes": "13:00"
    }
  ],
  "sameAs": [
    "https://facebook.com/errandscall",
    "https://twitter.com/errandscall",
    "https://instagram.com/errandscall",
    "https://linkedin.com/company/errandscall"
  ]
}
</script>
<header>
  <!-- Top bar -->
  <div class="topbar py-2">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="contact-info">
        <small>
          <i class="fas fa-phone-alt"></i> +27 78 944 4633 |
          <i class="fas fa-envelope"></i> info@errandscall.co.za
        </small>
      </div>
      <div class="social-links">
        <a href="https://facebook.com/errandscall" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com/errandscall" target="_blank"><i class="fab fa-twitter"></i></a>
        <a href="https://instagram.com/errandscall" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://linkedin.com/company/errandscall" target="_blank"><i class="fab fa-linkedin-in"></i></a>
      </div>
    </div>
  </div>

  <!-- Navigation - Now Sticky -->
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top nav-gradient" style="position: sticky; top: 0; z-index: 1030;">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img src="images/logo.png" alt="ErrandsCall Logo" style="max-height:50px;">
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item"><a class="nav-link <?= ($currentPage === 'index.php' || $currentPage === '') ? 'active' : '' ?>" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link <?= $currentPage === 'services.php' ? 'active' : '' ?>" href="services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link <?= $currentPage === 'faq.php' ? 'active' : '' ?>" href="faq.php">FAQ</a></li>
          <li class="nav-item"><a class="nav-link <?= $currentPage === 'team.php' ? 'active' : '' ?>" href="team.php">Team</a></li>
          <li class="nav-item"><a class="nav-link <?= $currentPage === 'downloads.php' ? 'active' : '' ?>" href="downloads.php">Downloads</a></li>
          <li class="nav-item"><a class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>" href="contact.php">Contact</a></li>
          <!--<li class="nav-item"><a class="btn btn-light btn-sm ml-2" href="portal/index.php">Login</a></li> -->
          <li class="nav-item"><a class="btn btn-warning btn-sm ml-2" href="portal/index.php">Login</a></li>
        </ul>
      </div>
    </div>
  </nav>
</header>