(function () {
  var STORAGE_KEY = 'asclepius_cookie_prefs';

  function readPrefs() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch (e) {
      return null;
    }
  }

  function writePrefs(prefs) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
    hideBanner();
    syncPreferenceForm(prefs);
  }

  function hideBanner() {
    var banner = document.getElementById('cookie-banner');
    if (banner) {
      banner.hidden = true;
    }
  }

  function showBanner() {
    var banner = document.getElementById('cookie-banner');
    if (banner) {
      banner.hidden = false;
    }
  }

  function syncPreferenceForm(prefs) {
    var analytics = document.getElementById('pref-analytics');
    if (analytics) {
      analytics.checked = !!prefs.analytics;
    }
  }

  window.asclepiusCookiePrefs = readPrefs();

  document.addEventListener('DOMContentLoaded', function () {
    var prefs = readPrefs();
    if (!prefs || !prefs.saved) {
      showBanner();
    } else {
      syncPreferenceForm(prefs);
    }

    var acceptBtn = document.getElementById('cookie-accept');
    var rejectBtn = document.getElementById('cookie-reject');
    var saveBtn = document.getElementById('cookie-save');

    if (acceptBtn) {
      acceptBtn.addEventListener('click', function () {
        writePrefs({ essential: true, analytics: true, saved: true });
      });
    }

    if (rejectBtn) {
      rejectBtn.addEventListener('click', function () {
        writePrefs({ essential: true, analytics: false, saved: true });
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        var analytics = document.getElementById('pref-analytics');
        writePrefs({
          essential: true,
          analytics: analytics ? analytics.checked : false,
          saved: true
        });
        var status = document.getElementById('cookie-save-status');
        if (status) {
          status.textContent = 'Your preferences have been saved.';
        }
      });
    }
  });
})();
