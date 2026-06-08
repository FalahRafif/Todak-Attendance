@if(request()->routeIs('booking.page'))
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@php
  $bookingFormScriptPath = public_path('assets/pages/public/booking-page/form.js');
  $bookingFormScriptVersion = file_exists($bookingFormScriptPath) ? filemtime($bookingFormScriptPath) : time();
@endphp
<script src="{{ asset('assets/pages/public/booking-page/form.js') }}?v={{ $bookingFormScriptVersion }}"></script>
@endif

@if(request()->routeIs('booking.status'))
@php
  $bookingStatusScriptPath = public_path('assets/pages/public/booking-page/status.js');
  $bookingStatusScriptVersion = file_exists($bookingStatusScriptPath) ? filemtime($bookingStatusScriptPath) : time();
@endphp
<script src="{{ asset('assets/pages/public/booking-page/status.js') }}?v={{ $bookingStatusScriptVersion }}"></script>
@endif

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.public-header');
    const toggle = document.querySelector('.menu-toggle');
    const menu = document.getElementById('public-menu');

    if (header && toggle && menu) {
      header.classList.add('js-ready');

      const closeMenu = function () {
        header.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      };

      const openMenu = function () {
        header.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
      };

      toggle.addEventListener('click', function () {
        if (header.classList.contains('is-open')) {
          closeMenu();
        } else {
          openMenu();
        }
      });

      menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', closeMenu);
      });

      document.addEventListener('click', function (event) {
        if (!header.contains(event.target)) {
          closeMenu();
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          closeMenu();
        }
      });
    }

    const bookingTabs = document.querySelector('[data-booking-tabs]');
    if (bookingTabs) {
      const tabButtons = bookingTabs.querySelectorAll('[data-booking-tab]');
      const tabPanels = bookingTabs.querySelectorAll('[data-booking-panel]');

      const setActiveTab = function (tabName) {
        tabButtons.forEach(function (button) {
          const isActive = button.getAttribute('data-booking-tab') === tabName;
          button.classList.toggle('is-active', isActive);
          button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        tabPanels.forEach(function (panel) {
          const isActive = panel.getAttribute('data-booking-panel') === tabName;
          panel.classList.toggle('is-active', isActive);
          panel.hidden = !isActive;
        });

        document.dispatchEvent(new CustomEvent('booking:tabChanged', {
          detail: { tab: tabName }
        }));
      };

      tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          setActiveTab(button.getAttribute('data-booking-tab'));
        });
      });

      bookingTabs.querySelectorAll('[data-booking-tab-cta]').forEach(function (button) {
        button.addEventListener('click', function () {
          const targetTab = button.getAttribute('data-booking-tab-cta');
          setActiveTab(targetTab);

          const targetPanel = bookingTabs.querySelector('[data-booking-panel="' + targetTab + '"]');
          if (targetPanel) {
            targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        });
      });

      const initialButton = bookingTabs.querySelector('.booking-tab.is-active');
      if (initialButton) {
        setActiveTab(initialButton.getAttribute('data-booking-tab'));
      } else if (tabButtons.length) {
        setActiveTab(tabButtons[0].getAttribute('data-booking-tab'));
      }
    }

    const dateCheckInput = document.getElementById('booking_date_check');
    const sessionSelect = document.getElementById('booking_session');
    const summary = document.getElementById('availability_summary');
    const morningCard = document.querySelector('[data-slot-card="morning"]');
    const eveningCard = document.querySelector('[data-slot-card="evening"]');
    const morningStatus = document.querySelector('[data-slot-status="morning"]');
    const eveningStatus = document.querySelector('[data-slot-status="evening"]');
    if (dateCheckInput && summary && morningCard && eveningCard && morningStatus && eveningStatus) {
      const availabilityEndpoint = dateCheckInput.getAttribute('data-availability-url') || '';
      let renderRequestId = 0;

      const statusLabel = {
        available: 'Tersedia',
        limited: 'Tersisa 1 slot',
        full: 'Penuh',
        unknown: 'Belum dipilih'
      };

      function normalizeStatus(rawStatus) {
        const value = String(rawStatus || '').toLowerCase();
        if (value === 'full' || value === 'limited' || value === 'available' || value === 'unknown') {
          return value;
        }

        return 'unknown';
      }

      function renderSlot(card, statusEl, statusValue, detailLabel) {
        card.classList.remove('status-available', 'status-limited', 'status-full', 'status-unknown');
        card.classList.add('status-' + statusValue);
        statusEl.textContent = detailLabel || statusLabel[statusValue] || statusLabel.unknown;
      }

      function refreshSessionSelect() {
        if (!sessionSelect || !window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.select2 !== 'function') {
          return;
        }

        window.jQuery(sessionSelect).trigger('change.select2');
      }

      function syncSessionOptions(status, hasDate) {
        if (!sessionSelect) {
          return;
        }

        const morningAvailable = hasDate && status.morning !== 'full' && status.morning !== 'unknown';
        const eveningAvailable = hasDate && status.evening !== 'full' && status.evening !== 'unknown';
        let hasEnabledOption = false;

        Array.from(sessionSelect.options).forEach(function (option) {
          const sessionCode = String(option.getAttribute('data-session-code') || '').toUpperCase();
          if (option.value === '') {
            option.disabled = false;
            return;
          }

          if (sessionCode === 'ES_PAGI_SIANG') {
            option.disabled = !morningAvailable;
          } else if (sessionCode === 'ES_SORE_MALAM') {
            option.disabled = !eveningAvailable;
          } else {
            option.disabled = !hasDate;
          }

          if (!option.disabled) {
            hasEnabledOption = true;
          }
        });

        if (!hasEnabledOption || !hasDate) {
          sessionSelect.value = '';
          sessionSelect.disabled = true;
        } else {
          const selectedOption = sessionSelect.options[sessionSelect.selectedIndex];
          if (!selectedOption || selectedOption.disabled) {
            sessionSelect.value = '';
          }
          sessionSelect.disabled = false;
        }

        refreshSessionSelect();
      }

      function resolveFallbackAvailability(dateValue) {
        if (!dateValue) {
          return { morning: 'unknown', evening: 'unknown' };
        }

        const dayOfWeek = new Date(dateValue + 'T00:00:00').getDay();
        if (dayOfWeek === 0 || dayOfWeek === 6) {
          return { morning: 'limited', evening: 'available' };
        }

        return { morning: 'available', evening: 'available' };
      }

      async function fetchAvailability(dateValue) {
        if (!dateValue || availabilityEndpoint === '') {
          return {
            status: resolveFallbackAvailability(dateValue),
            detail: {
              morning: null,
              evening: null,
            },
          };
        }

        const requestUrl = new URL(availabilityEndpoint, window.location.origin);
        requestUrl.searchParams.set('date', dateValue);

        try {
          const response = await fetch(requestUrl.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
              Accept: 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
            },
          });

          if (!response.ok) {
            throw new Error('Gagal mengambil ketersediaan');
          }

          const payload = await response.json();
          const morning = payload?.slots?.morning ?? {};
          const evening = payload?.slots?.evening ?? {};

          return {
            status: {
              morning: normalizeStatus(morning.status),
              evening: normalizeStatus(evening.status),
            },
            detail: {
              morning: typeof morning.label === 'string' && morning.label !== '' ? morning.label : null,
              evening: typeof evening.label === 'string' && evening.label !== '' ? evening.label : null,
            },
          };
        } catch (_error) {
          return {
            status: resolveFallbackAvailability(dateValue),
            detail: {
              morning: null,
              evening: null,
            },
          };
        }
      }

      async function renderAvailability(dateValue) {
        const hasDate = Boolean(dateValue);
        const currentRequestId = ++renderRequestId;

        if (!hasDate) {
          renderSlot(morningCard, morningStatus, 'unknown', null);
          renderSlot(eveningCard, eveningStatus, 'unknown', null);
          syncSessionOptions({ morning: 'unknown', evening: 'unknown' }, false);
          summary.textContent = 'Pilih tanggal untuk melihat status slot.';
          return;
        }

        summary.textContent = 'Mengecek ketersediaan slot...';

        const availability = await fetchAvailability(dateValue);
        if (currentRequestId !== renderRequestId) {
          return;
        }

        const status = availability.status;
        const detail = availability.detail;

        renderSlot(morningCard, morningStatus, status.morning, detail.morning);
        renderSlot(eveningCard, eveningStatus, status.evening, detail.evening);
        syncSessionOptions(status, true);

        if (status.morning === 'full' && status.evening === 'full') {
          summary.textContent = 'Tanggal dipilih sudah penuh. Silakan pilih tanggal lain.';
          return;
        }

        if (status.morning === 'limited' || status.evening === 'limited') {
          summary.textContent = 'Tanggal dipilih masih tersedia terbatas. Segera kirim request untuk diproses admin.';
          return;
        }

        summary.textContent = 'Tanggal dipilih tersedia. Slot akan fix setelah DP berhasil diverifikasi.';
      }

      dateCheckInput.addEventListener('change', function () {
        renderAvailability(dateCheckInput.value);
      });

      renderAvailability(dateCheckInput.value);
    }
  });
</script>
</body>
</html>
