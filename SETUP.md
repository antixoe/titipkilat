# Titip Kilat — Setup Guide

**Sistem Informasi Manajemen Layanan Jasa Titip Antar Warga dan Jasa Titip Perjalanan Internasional Terintegrasi**

## Prerequisites

- PHP 8.2+
- Composer
- MySQL 8+ (running on localhost:3306)
- Flutter SDK 3.3+ (for frontend)
- Node.js & npm (for Vite/build)

---

## 1. Database Setup

Create the MySQL database before migrating:

```sql
CREATE DATABASE titipkilat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or via CLI:

```bash
mysql -u root -e "CREATE DATABASE titipkilat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## 2. Backend Setup (Laravel 12 + Sanctum)

The backend lives in the **project root** (not `/backend`, which is a skeleton).

```bash
# From the project root directory
composer install
cp .env.example .env
php artisan key:generate

# Run migrations & seeders
php artisan migrate:fresh --seed

# Start the Laravel dev server
php artisan serve
```

**Backend URL:** `http://localhost:8000`

### API Endpoints

| Method | Endpoint | Auth | Role | Description |
|--------|----------|------|------|-------------|
| POST | `/api/auth/register` | No | - | Register new user |
| POST | `/api/auth/login` | No | - | Login, returns token |
| POST | `/api/auth/logout` | Yes | Any | Revoke token |
| GET | `/api/me` | Yes | Any | Current user profile |
| GET | `/api/wallet` | Yes | Any | Wallet balance + transactions |
| POST | `/api/wallet/top-up` | Yes | Any | Top up (+ Rp 1.000 fee) |
| GET | `/api/shipping/quote` | Yes | Any | Shipping cost estimate |
| POST | `/api/orders` | Yes | USER | Create Antar Warga order |
| GET | `/api/orders/{id}` | Yes | Any | Order detail |
| POST | `/api/orders/{id}/claim` | Yes | COURIER | Claim order (kurir rebut) |
| PATCH | `/api/orders/{id}/dp` | Yes | COURIER | Set DP requirement |
| POST | `/api/orders/{id}/invoice` | Yes | COURIER | Upload invoice photo |
| PATCH | `/api/orders/{id}/status` | Yes | COURIER | Update delivery status |
| GET | `/api/trips` | Yes | Any | List open trips |
| POST | `/api/trips` | Yes | TRAVELER | Create trip schedule |
| POST | `/api/orders/{id}/accept-po` | Yes | TRAVELER | Accept pre-order |
| POST | `/api/orders/{id}/purchase-photo` | Yes | TRAVELER | Upload purchase proof |
| PATCH | `/api/orders/{id}/ship` | Yes | TRAVELER | Mark as shipped |
| POST | `/api/orders/{id}/complete` | Yes | USER | Settle & complete order |
| POST | `/api/orders/{id}/rating` | Yes | USER | Rate courier/traveler |
| POST | `/api/orders/{id}/dispute` | Yes | Any | Submit dispute |
| GET | `/api/admin/transactions/active` | Yes | ADMIN | Active transactions |
| GET | `/api/admin/disputes` | Yes | ADMIN | Open disputes |
| PATCH | `/api/admin/disputes/{id}` | Yes | ADMIN | Resolve dispute |
| PATCH | `/api/admin/users/{id}/kyc` | Yes | ADMIN | Verify KYC |
| GET | `/api/super-admin/ledger` | Yes | SUPER_ADMIN | Wallet ledger audit |
| GET | `/api/super-admin/fees` | Yes | SUPER_ADMIN | List fee settings |
| PATCH | `/api/super-admin/fees/{id}` | Yes | SUPER_ADMIN | Update fee |
| GET | `/api/super-admin/monitoring` | Yes | SUPER_ADMIN | System monitoring |

### Test Credentials (after seeding)

| Role | Email | Password |
|------|-------|----------|
| USER | user@titipkilat.test | password |
| COURIER | courier@titipkilat.test | password |
| TRAVELER | traveler@titipkilat.test | password |
| ADMIN | admin@titipkilat.test | password |
| SUPER ADMIN | superadmin@titipkilat.test | password |

### Fee Defaults

| Fee | Amount |
|-----|--------|
| App Platform Fee | Rp 1.000 / transaction |
| Wallet Top-Up Fee | Rp 1.000 / top-up |
| Courier Fee | Rp 1.000 / item |
| Shipping Base | Rp 5.000 / lb |
| Shipping Per Km | Rp 100 / km |

---

## 3. Frontend Setup (Flutter Web)

The Flutter web app lives in `/frontend`.

```bash
cd frontend
flutter pub get
flutter run -d chrome --web-port=8080
```

**Frontend URL:** `http://localhost:8080`

### Branding

- Primary Red: `#E53935`
- Accent Orange: `#FB8C00`
- Neutral White: `#FFFFFF`
- Dark Slate: `#1E293B`

---

## 4. Quick Start (All at Once)

```bash
# Terminal 1: Backend
cd /path/to/project
php artisan serve

# Terminal 2: Frontend
cd frontend
flutter run -d chrome --web-port=8080
```

---

## Architecture

```
/
├── app/                    # Laravel app code
│   ├── Http/Controllers/Api/   # All API controllers
│   ├── Http/Middleware/        # RoleMiddleware (RBAC)
│   ├── Models/                # Eloquent models
│   ├── Providers/             # Service providers
│   └── Services/              # ShippingCalculator
├── database/
│   ├── migrations/            # All DB migrations
│   └── seeders/               # DatabaseSeeder (roles, users, wallets, fees)
├── routes/
│   ├── api.php                # All REST API routes
│   └── web.php                # Web view routes
├── frontend/                  # Flutter web app
│   ├── lib/main.dart          # Single-file Flutter app
│   ├── pubspec.yaml
│   └── web/index.html
├── .env                       # MySQL config (titipkilat DB)
└── SETUP.md
```

---

## Business Flows

### Flow A: Antar Warga (Local Proxy Delivery)

1. User creates order → Status: `SEARCHING_COURIER`
2. Courier claims via `POST /orders/{id}/claim` (atomic DB lock)
3. Courier sets DP: `PATCH /orders/{id}/dp`
4. Courier uploads invoice: `POST /orders/{id}/invoice`
5. Courier updates status: `PATCH /orders/{id}/status`
6. User settles: `POST /orders/{id}/complete` → Wallet deduction
7. User rates: `POST /orders/{id}/rating`

### Flow B: Antar Negara (International Jastip)

1. Traveler creates trip: `POST /trips` → Status: `TRIP_OPEN`
2. User creates PO under trip: `POST /orders` (type=INTERNATIONAL_PO)
3. Traveler accepts: `POST /orders/{id}/accept-po`
4. Traveler purchases abroad: `POST /orders/{id}/purchase-photo`
5. Traveler ships: `PATCH /orders/{id}/ship`
6. User settles: `POST /orders/{id}/complete`
7. User rates: `POST /orders/{id}/rating`
