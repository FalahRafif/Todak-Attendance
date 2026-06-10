@push('styles')
<style>
    .ka-hrd-shell { max-width: 1180px; margin: 0 auto; }
    .ka-hrd-toolbar { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; margin-bottom: 1rem; }
    .ka-hrd-grid { display: grid; gap: 1rem; }
    .ka-hrd-card-list { display: none; gap: .85rem; }
    .ka-hrd-item { display: block; border: 1px solid #e5edf7; border-radius: 18px; padding: 1rem; background: #fff; color: inherit; text-decoration: none; box-shadow: 0 8px 24px rgba(15, 76, 129, .05); }
    .ka-hrd-item-title { font-weight: 800; color: #0f172a; }
    .ka-hrd-meta { color: #64748b; font-size: .84rem; }
    .ka-hrd-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: .28rem .65rem; background: #eff6ff; color: #0f4c81; font-size: .76rem; font-weight: 800; }
    .ka-hrd-stat { border-radius: 20px; background: linear-gradient(135deg, #f8fbff, #fff); border: 1px solid #e5edf7; box-shadow: 0 8px 24px rgba(15, 76, 129, .05); }
    .ka-hrd-stat span { color: #64748b; font-size: .8rem; }
    .ka-hrd-stat h3 { margin: .35rem 0 0; }
    .ka-detail-box { border: 1px solid #e5edf7; border-radius: 16px; padding: .9rem; height: 100%; background: #fff; }
    .ka-detail-box b { display: block; color: #64748b; font-size: .78rem; margin-bottom: .25rem; }
    @media (max-width: 767.98px) {
        .ka-hrd-shell { max-width: 100%; }
        .ka-hrd-toolbar { flex-direction: column; }
        .ka-hrd-toolbar .btn { width: 100%; }
        .ka-hrd-table { display: none; }
        .ka-hrd-card-list { display: grid; }
        .ka-hrd-filter .form-control, .ka-hrd-filter .btn, .ka-hrd-filter a.btn { min-height: 44px; }
    }
</style>
@endpush
