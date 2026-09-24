
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!-- Floating WhatsApp button: opens a small form so the user can edit the message before sending.
     The href stays as a fallback if JavaScript is disabled. -->
<a href="https://wa.me/27789444633?text=Hi%2C%20please%20can%20you%20assist%20me%3F" class="floatWhatsapp" id="waToggle" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp" aria-controls="waPopup" aria-expanded="false"><i class="fab fa-whatsapp my-float"></i></a>
<div class="wa-popup" id="waPopup" role="dialog" aria-labelledby="waPopupTitle" hidden>
  <div class="wa-popup-header">
    <i class="fab fa-whatsapp"></i>
    <div>
      <strong id="waPopupTitle">Chat with ErrandsCall</strong>
      <span>We usually reply within minutes</span>
    </div>
    <button type="button" class="wa-popup-close" id="waClose" aria-label="Close">&times;</button>
  </div>
  <form class="wa-popup-body" id="waForm">
    <label for="waMessage">Your message</label>
    <textarea id="waMessage" rows="4" maxlength="1000" required>Hi, please can you assist me?</textarea>
    <button type="submit" class="wa-popup-send"><i class="fab fa-whatsapp"></i> Send on WhatsApp</button>
  </form>
</div>
<script>
(function () {
  var toggle = document.getElementById('waToggle');
  var popup = document.getElementById('waPopup');
  var message = document.getElementById('waMessage');

  function setOpen(open) {
    popup.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) message.focus();
  }

  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    setOpen(popup.hidden);
  });

  document.getElementById('waClose').addEventListener('click', function () {
    setOpen(false);
    toggle.focus();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !popup.hidden) { setOpen(false); toggle.focus(); }
  });

  document.addEventListener('click', function (e) {
    if (!popup.hidden && !popup.contains(e.target) && !toggle.contains(e.target)) setOpen(false);
  });

  document.getElementById('waForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var text = message.value.trim();
    if (!text) { message.focus(); return; }
    window.open('https://wa.me/27789444633?text=' + encodeURIComponent(text), '_blank', 'noopener');
    setOpen(false);
  });
})();
</script>
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
    "streetAddress": "1 Chadwick Avenue, Cnr Andries Street, Wynberg",
    "addressLocality": "Sandton",
    "addressRegion": "Gauteng",
    "postalCode": "2090",
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
    "https://www.facebook.com/ErrandsCall/",
    "https://x.com/Errandscall",
    "https://instagram.com/errandscall",
    "https://www.linkedin.com/company/errandscall"
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
        <a href="https://www.facebook.com/ErrandsCall/" target="_blank" rel="noopener" aria-label="ErrandsCall on Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://x.com/Errandscall" target="_blank" rel="noopener" aria-label="ErrandsCall on X"><i class="fab fa-twitter"></i></a>
        <a href="https://instagram.com/errandscall" target="_blank" rel="noopener" aria-label="ErrandsCall on Instagram"><i class="fab fa-instagram"></i></a>
        <a href="https://www.linkedin.com/company/errandscall" target="_blank" rel="noopener" aria-label="ErrandsCall on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
      </div>
    </div>
  </div>
</header>

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
          <li class="nav-item"><a class="btn btn-warning btn-sm ml-2" href="portal/index.php" rel="nofollow"><i class="fas fa-user-lock mr-1"></i>Client Login</a></li>
        </ul>
      </div>
    </div>
</nav>