# Layout Checklist

- [x] Convert `resources/views/layouts/admin/admin.blade.php` into a real master layout.
- [x] Keep the Nowa structural order: assets, switcher, header, sidebar, content, footer, right sidebar, scripts.
- [x] Preserve reusable partials from `resources/views/layouts/admin/` and compose them with Blade includes.
- [x] Expose a content section for page-level rendering.
- [x] Expose style and script stacks for page-specific assets.
- [x] Keep the raw Nowa markup reference in `resources/views/pages/admin/blank.blade.html` unchanged for fidelity comparison.
- [x] Create a valid Blade page at `resources/views/pages/admin/blank.blade.php`.
- [x] Ensure the blank page extends the admin master layout.
- [x] Ensure the blank page reproduces the reference breadcrumb and card structure.
- [x] Keep the admin blank route grouped under the `admin` prefix and `admin.` name prefix.
- [x] Keep the blank controller resolving the Blade-valid view.
- [x] Verify the route renders without view resolution errors.
- [x] Verify the page loads with the admin layout shell and blank content visible.
- [x] Verify local rendering with the Laragon PHP binary at `C:\laragon\bin\php\php-8.3.29-Win32-vs16-x64\php.exe`.
- [x] Clear Laravel view cache before final visual verification.
- [ ] Confirm the implementation is production-safe for future admin pages.