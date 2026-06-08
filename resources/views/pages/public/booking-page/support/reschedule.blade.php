@extends('layouts.guest.guest')

@section('content')
<section class="section-block container booking-section">
  <div class="section-heading booking-heading">
    <p class="eyebrow">Reschedule Request</p>
    <h2>Ajukan perubahan jadwal acara</h2>
    <p class="section-lead">Request reschedule diproses manual dan hanya dapat diajukan maksimal 14 hari sebelum tanggal acara.</p>
  </div>

  <div class="booking-grid booking-grid-single">
    <form class="booking-panel" id="reschedule_form" method="post">
      <h3>Form Request Reschedule</h3>
      <div class="booking-form-grid">
        <div class="form-field">
          <label class="form-label" for="reschedule_booking_code">Kode Booking</label>
          <input class="form-input" type="text" id="reschedule_booking_code" name="booking_code" placeholder="Contoh: ETH-20260505-00001" required>
        </div>
        <div class="form-field">
          <label class="form-label" for="reschedule_phone_last4">4 Digit Terakhir No WhatsApp</label>
          <input class="form-input" type="tel" id="reschedule_phone_last4" name="phone_last4" maxlength="4" pattern="\d{4}" placeholder="Contoh: 1234" required>
        </div>
        <div class="form-field">
          <label class="form-label" for="reschedule_date_new">Tanggal Baru (Usulan)</label>
          <input class="form-input" type="date" id="reschedule_date_new" name="proposed_date" required>
        </div>
        <div class="form-field form-field-full">
          <label class="form-label" for="reschedule_reason">Alasan Reschedule</label>
          <textarea class="form-input form-textarea" id="reschedule_reason" name="reason" rows="4" placeholder="Tuliskan alasan perubahan jadwal acara." required></textarea>
        </div>
      </div>
      <div id="reschedule_message" class="booking-alert" style="display:none;"></div>
      <p class="booking-actions"><button class="cta booking-submit" type="submit" id="reschedule_submit_btn">Kirim Request</button></p>
    </form>
  </div>

  @include('pages.public.booking-page.sections.support-links')
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('reschedule_form');
  var msgEl = document.getElementById('reschedule_message');
  var btn = document.getElementById('reschedule_submit_btn');
  if (!form || !msgEl || !btn) return;

  function showMessage(text, isSuccess) {
    msgEl.textContent = text;
    msgEl.style.display = 'block';
    msgEl.style.background = isSuccess ? '#d4edda' : '#f8d7da';
    msgEl.style.color = isSuccess ? '#155724' : '#721c24';
    msgEl.style.border = isSuccess ? '1px solid #c3e6cb' : '1px solid #f5c6cb';
    msgEl.style.padding = '0.75rem 1rem';
    msgEl.style.borderRadius = '0.5rem';
    msgEl.style.marginBottom = '1rem';
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    var bookingCode = document.getElementById('reschedule_booking_code').value.trim();
    var phoneLast4 = document.getElementById('reschedule_phone_last4').value.trim();
    var proposedDate = document.getElementById('reschedule_date_new').value;
    var reason = document.getElementById('reschedule_reason').value.trim();

    if (!bookingCode || !phoneLast4 || !proposedDate || !reason) {
      showMessage('Semua field wajib diisi.', false);
      return;
    }

    btn.disabled = true;
    btn.textContent = 'Mengirim...';
    msgEl.style.display = 'none';

    fetch('/api/booking/reschedule-request', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        booking_code: bookingCode,
        phone_last4: phoneLast4,
        proposed_date: proposedDate,
        reason: reason
      })
    })
    .then(function(resp) { return resp.json(); })
    .then(function(data) {
      if (data.message) {
        var ok = !data.errors;
        showMessage(data.message, ok);
        if (ok) {
          form.reset();
        }
      }
    })
    .catch(function() {
      showMessage('Terjadi kesalahan. Silakan coba lagi.', false);
    })
    .finally(function() {
      btn.disabled = false;
      btn.textContent = 'Kirim Request';
    });
  });
});
</script>
@endpush
