<?php
// Cookie consent banner (POPIA). Included from includes/footer.php on every public page.
// Google Analytics, Google Ads and the Meta Pixel are only loaded after the visitor
// agrees to the matching category. IDs live in includes/site-config.php.
require_once __DIR__ . '/site-config.php';
?>
<div class="cookie-consent" id="cookieConsent" role="dialog" aria-labelledby="cookieConsentTitle" aria-describedby="cookieConsentText" hidden>
  <div class="cookie-consent-inner">
    <div class="cookie-consent-copy">
      <h2 class="cookie-consent-title" id="cookieConsentTitle"><i class="fas fa-cookie-bite"></i> We value your privacy</h2>
      <p id="cookieConsentText">
        We use essential cookies to make this site work. With your permission, we'd also like to use
        analytics cookies to understand how the site is used, and marketing cookies (Google and Meta)
        to show you relevant ads. Read our <a href="privacy.php#cookies">Privacy Policy</a>.
      </p>
    </div>

    <div class="cookie-consent-options" id="cookieConsentOptions" hidden>
      <label class="cookie-option">
        <input type="checkbox" checked disabled>
        <span><strong>Essential</strong> Always on. Remembers your cookie choice and keeps the site working.</span>
      </label>
      <label class="cookie-option">
        <input type="checkbox" id="cookieAnalytics">
        <span><strong>Analytics</strong> Google Analytics: anonymous statistics on how visitors use the site.</span>
      </label>
      <label class="cookie-option">
        <input type="checkbox" id="cookieMarketing">
        <span><strong>Marketing</strong> Google Ads and Meta (Facebook, Instagram): measures our ads and shows you relevant ones.</span>
      </label>
    </div>

    <div class="cookie-consent-actions">
      <button type="button" class="btn btn-sm cookie-btn-secondary" id="cookieCustomise">Customise</button>
      <button type="button" class="btn btn-sm cookie-btn-secondary" id="cookieSave" hidden>Save choices</button>
      <button type="button" class="btn btn-sm cookie-btn-secondary" id="cookieReject">Reject all</button>
      <button type="button" class="btn btn-sm btn-primary" id="cookieAccept">Accept all</button>
    </div>
  </div>
</div>
<script>
(function () {
  var CONFIG = {
    ga4: <?php echo json_encode(GA4_MEASUREMENT_ID); ?>,
    ads: <?php echo json_encode(GOOGLE_ADS_ID); ?>,
    meta: <?php echo json_encode(META_PIXEL_ID); ?>
  };
  var STORAGE_KEY = 'ec_cookie_consent';
  var VERSION = 1; // bump to ask everyone again after the cookie policy changes

  var banner = document.getElementById('cookieConsent');
  var options = document.getElementById('cookieConsentOptions');
  var analyticsBox = document.getElementById('cookieAnalytics');
  var marketingBox = document.getElementById('cookieMarketing');
  var customiseBtn = document.getElementById('cookieCustomise');
  var saveBtn = document.getElementById('cookieSave');

  function readConsent() {
    try {
      var c = JSON.parse(localStorage.getItem(STORAGE_KEY));
      return c && c.v === VERSION ? c : null;
    } catch (e) { return null; }
  }

  function writeConsent(c) {
    try { localStorage.setItem(STORAGE_KEY, JSON.stringify(c)); } catch (e) {}
  }

  // ---- Tag loading (runs only after consent) ----
  var googleLoaded = false, metaLoaded = false;

  function loadGoogle(c) {
    var wantAnalytics = c.analytics && CONFIG.ga4;
    var wantAds = c.marketing && CONFIG.ads;
    if (!wantAnalytics && !wantAds) return;

    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { dataLayer.push(arguments); };
    if (!googleLoaded) {
      gtag('consent', 'default', {
        analytics_storage: 'denied', ad_storage: 'denied',
        ad_user_data: 'denied', ad_personalization: 'denied'
      });
      var s = document.createElement('script');
      s.async = true;
      s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(wantAnalytics ? CONFIG.ga4 : CONFIG.ads);
      document.head.appendChild(s);
      gtag('js', new Date());
      googleLoaded = true;
    }
    gtag('consent', 'update', {
      analytics_storage: c.analytics ? 'granted' : 'denied',
      ad_storage: c.marketing ? 'granted' : 'denied',
      ad_user_data: c.marketing ? 'granted' : 'denied',
      ad_personalization: c.marketing ? 'granted' : 'denied'
    });
    if (wantAnalytics) gtag('config', CONFIG.ga4);
    if (wantAds) gtag('config', CONFIG.ads);
  }

  function loadMeta(c) {
    if (!c.marketing || !CONFIG.meta || metaLoaded) return;
    /* Meta Pixel base code */
    !function (f, b, e, v, n, t, s) {
      if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); };
      if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = [];
      t = b.createElement(e); t.async = !0; t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s);
    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', CONFIG.meta);
    fbq('track', 'PageView');
    metaLoaded = true;
  }

  function applyConsent(c) {
    loadGoogle(c);
    loadMeta(c);
  }

  // Remove tracking cookies that were set before the visitor withdrew consent.
  function clearTrackingCookies(c) {
    var prefixes = [];
    if (!c.analytics) prefixes.push('_ga', '_gid');
    if (!c.marketing) prefixes.push('_gcl', '_fbp', '_fbc');
    if (!prefixes.length) return;
    var host = location.hostname;
    var domains = ['', host, '.' + host, '.' + host.split('.').slice(-3).join('.'), '.' + host.split('.').slice(-2).join('.')];
    document.cookie.split(';').forEach(function (part) {
      var name = part.split('=')[0].trim();
      if (!prefixes.some(function (p) { return name.indexOf(p) === 0; })) return;
      domains.forEach(function (d) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/' + (d ? '; domain=' + d : '');
      });
    });
  }

  // ---- Banner UI ----
  function showOptions(show) {
    options.hidden = !show;
    saveBtn.hidden = !show;
    customiseBtn.hidden = show;
  }

  function open(withOptions) {
    var c = readConsent();
    analyticsBox.checked = !!(c && c.analytics);
    marketingBox.checked = !!(c && c.marketing);
    showOptions(!!withOptions);
    banner.hidden = false;
  }

  function save(analytics, marketing) {
    var previous = readConsent();
    var c = { v: VERSION, analytics: analytics, marketing: marketing, date: new Date().toISOString() };
    writeConsent(c);
    banner.hidden = true;
    clearTrackingCookies(c);
    // Scripts that already loaded can't be unloaded, so reload when consent is withdrawn.
    if (previous && ((previous.analytics && !analytics) || (previous.marketing && !marketing))) {
      location.reload();
      return;
    }
    applyConsent(c);
  }

  document.getElementById('cookieAccept').addEventListener('click', function () { save(true, true); });
  document.getElementById('cookieReject').addEventListener('click', function () { save(false, false); });
  customiseBtn.addEventListener('click', function () { showOptions(true); });
  saveBtn.addEventListener('click', function () { save(analyticsBox.checked, marketingBox.checked); });

  // Footer "Cookie settings" link (and any element with data-cookie-settings) reopens the banner.
  document.addEventListener('click', function (e) {
    var t = e.target.closest && e.target.closest('[data-cookie-settings]');
    if (!t) return;
    e.preventDefault();
    open(true);
  });

  var existing = readConsent();
  if (existing) applyConsent(existing);
  else open(false);
})();
</script>
