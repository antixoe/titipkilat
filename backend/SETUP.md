# Titip Kilat MVP

Backend Laravel berada di `/backend`; Flutter Web client berada di `/frontend`.

## MySQL

Buat database sekali jika belum ada:

```sql
CREATE DATABASE titipkilat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lalu jalankan backend:

```powershell
cd D:\titipkilat\backend
composer install
php artisan migrate --seed
php artisan serve --host=localhost --port=8000
```

Jalankan client pada terminal lain:

```powershell
cd D:\titipkilat\frontend
flutter pub get
flutter run -d chrome --web-port 8080
```

Seed users memakai password `password`. API base URL: `http://localhost:8000/api`.

Authentication uses Laravel Sanctum bearer tokens. Send `Authorization: Bearer <token>` to protected endpoints; revoke the current device token with `POST /api/auth/logout`.

Local API sessions use the file driver, so the API does not depend on a `sessions` table. If database-backed sessions are required, set `SESSION_DRIVER=database` and run `php artisan migrate` first.

RBAC roles: `USER`, `COURIER`, `TRAVELER`, `ADMIN`, and `OPERATOR`. Role-protected routes use Laravel middleware (`role:USER`, `role:COURIER`, etc.).

Platform fee is `Rp 1.000` per completed transaction by default. It is deducted from the customer's wallet only through `POST /api/orders/{order}/complete`, after `DELIVERED` or `SHIPPED`; the fee is configurable by SUPER_ADMIN.

Courier extra fee defaults to `Rp 1.000` per purchased item. The order stores `courier_fee = courier_fee_setting * item_count`; this setting is configurable by SUPER_ADMIN.

Every wallet transaction stores exactly one positive ledger direction: `credit` or `debit`, together with its `type`. Top-ups create credits; platform charges create debits.

Settlement endpoint `POST /api/orders/{order}/complete` atomically debits the customer and credits the courier/traveler. Customer ratings are submitted with `POST /api/orders/{order}/rating` after completion.
