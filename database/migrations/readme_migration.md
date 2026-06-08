# Migration README

Dokumentasi singkat untuk menjalankan migration per versi folder.

## Migrate All

```bash
php artisan migrate
```

## Migrate Rollback ALL or base on batch migrate

```bash
php artisan migrate:rollback
```

## Reset Per Versi

Perintah ini akan rollback semua migration yang ada di folder versi tersebut:

```bash
php artisan migrate:reset --path=database/migrations/1.1.4
```

Untuk environment production:

```bash
php artisan migrate:reset --path=database/migrations/1.1.4 --force
```

## Migrate Per Versi

Jika ingin menjalankan migration untuk satu versi tertentu saja:

```bash
php artisan migrate --path=database/migrations/1.1.4
```

## Catatan

- Pastikan folder versi sudah di-load pada `AppServiceProvider` lewat `loadMigrationsFrom(...)`.
- Nama file migration harus unik dan urut sesuai prefix angka agar urutan eksekusi konsisten.
