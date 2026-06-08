# Public Layout Checklist

1. Buat direktori layout dan partial: `resources/views/layouts/public/` - SELESAI
2. Tambahkan CSS kustom: `public/assets/css/public-custom.css` - SELESAI
3. Buat landing page dan section di bawah `resources/views/pages/public/landing-page/` - SELESAI
4. Buat `LandingPageController` - SELESAI
5. Tambahkan route `/` ke landing page - SELESAI
6. Verifikasi view ter-render dan aset termuat - SEBAGIAN (render sisi server OK; snapshot browser OK tetapi ada beberapa gambar/font 404)
	- Catatan: font `InstrumentSans-Regular.woff2` dan gambar portofolio tidak ditemukan di `public/assets/images/portfolio/` (placeholder ditampilkan).
7. Verifikasi visual (desktop + mobile) - SEBAGIAN (struktur dan layout ter-render; tinjauan manual disarankan untuk jarak, gambar, dan polesan mobile)
8. Gunakan `icon.jpg` sebagai placeholder logo dan cadangan untuk gambar yang hilang - SELESAI
9. Terapkan penyempurnaan visual sinematik dan elegan pada CSS serta overlay hero - SELESAI
10. Sertakan library vendor admin (Bootstrap, ikon) untuk akses utilitas layout - SELESAI (include bersyarat)
11. Perbarui portofolio agar menampilkan gambar hero besar + grid, serta gaya rounded/masked - SELESAI
12. Sempurnakan spacing, tipografi, dan microcopy navigasi - SELESAI (paket, testimoni, FAQ, footer sudah didesain ulang)
13. Ganti placeholder portofolio dengan gambar berbeda dari `public/assets/images/photos/` - SELESAI (memakai foto yang sudah ada)

Perbarui status setelah langkah verifikasi selesai.
