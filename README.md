# CargoTrace — Global Shipment Visibility & Tracking Platform

> Final Project (UAS) · Web Programming 2 · Laravel 13

CargoTrace is a full-featured enterprise logistics web application built with Laravel 13. It provides real-time shipment tracking, role-based access control, a REST API, interactive maps, weather integration, and automated testing — all wrapped in a clean Dark Navy & Warm Beige UI.

---

## Features

- **Role-Based Access Control (RBAC)** — Admin, Operator, and Customer roles with scoped permissions via Laravel Policies
- **Shipment Lifecycle Management** — Strict state machine: `CREATED → BOOKED → PICKED_UP → IN_TRANSIT → DELIVERED`
- **Interactive Leaflet.js Map** — Custom animated pins for origin, delivery point, checkpoints, and exceptions with route polyline
- **Weather Widget** — Live weather data at destination via Open-Meteo API (cached, with fallback)
- **Dashboard Analytics** — KPI cards + Chart.js charts (shipment status distribution, monthly trends)
- **REST API** — 19 endpoints secured with Laravel Sanctum token authentication
- **Automated Tests** — 38 tests / 96 assertions covering auth, CRUD, lifecycle, and ETA delay logic
- **Service Layer Architecture** — All business logic in `app/Services/` (ShipmentService, TrackingService, EtaService, ExceptionService, WeatherService, AuditLogService)
- **Audit Logging** — Every state change is recorded with actor, action, and timestamp
- **Exception Management** — Raise and resolve shipment exceptions with operator workflows

---

## Database Schema (9 Tables)

| Table | Description |
|---|---|
| `users` | Authentication + role (admin / operator / customer) |
| `carriers` | Shipping carrier registry |
| `locations` | Port/hub locations with lat/lng coordinates |
| `shipments` | Core shipment records with status, ETA, delay tracking |
| `shipment_items` | Line items / cargo manifest per shipment |
| `tracking_events` | Append-only immutable tracking timeline |
| `shipment_exceptions` | Raised issues with resolution workflow |
| `notifications` | In-app notifications per user |
| `audit_logs` | Full audit trail of all system actions |

---

## Getting Started

### Requirements

- PHP 8.3+
- Composer
- MySQL 8.x
- Node.js 18+ & npm
- Laravel 13

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/ChairinaAzzahra/cargotrace-uas.git
cd cargotrace-uas

# 2. Install PHP dependencies
composer install

# 3. Install frontend dependencies
npm install

# 4. Copy environment file and configure
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
# DB_CONNECTION=mysql
# DB_DATABASE=cargotrace
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Run migrations and seed demo data
php artisan migrate:fresh --seed

# 7. Build frontend assets
npm run build

# 8. Start the development server
php artisan serve
```

Visit `http://localhost:8000`

---

## Demo Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@cargotrace.com | password |
| Operator | operator@cargotrace.com | password |
| Customer | customer@globaltrade.com | password |
| Customer | customer@asiapacific.com | password |

---

## Project Structure
app/ ├── Http/ │ ├── Controllers/ # Web & API controllers │ ├── Middleware/ # RoleMiddleware (RBAC) │ └── Requests/ # Form Request validation ├── Models/ # Eloquent models with relationships ├── Policies/ # ShipmentPolicy (authorization) └── Services/ # Business logic layer ├── ShipmentService.php ├── TrackingService.php ├── EtaService.php ├── ExceptionService.php ├── WeatherService.php └── AuditLogService.php

routes/ ├── web.php # 19 web routes └── api.php # 19 REST API endpoints

resources/views/ ├── layouts/ # app, navigation, guest layouts ├── dashboard.blade.php # KPI + charts ├── shipments/ # index, show, create, edit ├── carriers/ ├── locations/ ├── audit-logs/ └── notifications/

tests/Feature/ ├── AuthenticationTest.php ├── ShipmentManagementTest.php ├── ShipmentLifecycleTest.php └── EtaDelayTest.php



---

## REST API

Base URL: `/api`  
Authentication: Bearer token via Laravel Sanctum (`POST /api/login`)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/login` | Obtain API token |
| POST | `/api/logout` | Revoke token |
| GET | `/api/shipments` | List shipments |
| POST | `/api/shipments` | Create shipment |
| GET | `/api/shipments/{id}` | Get shipment detail |
| PUT | `/api/shipments/{id}` | Update shipment |
| DELETE | `/api/shipments/{id}` | Delete shipment |
| POST | `/api/shipments/{id}/transition` | Transition status |
| GET | `/api/carriers` | List carriers |
| GET | `/api/locations` | List locations |
| GET | `/api/exceptions` | List exceptions |
| POST | `/api/exceptions/{id}/resolve` | Resolve exception |

> Full Postman collection: `cargotrace_postman_collection.json`

---

##  Running Tests

```bash
php artisan test
# 38 tests, 96 assertions — all passing





A personal note reflection:
Jujur, saya tidak mengerjakan proyek ini dengan sungguh-sungguh selama satu bulan ini.
Saya kehilangan motibasi dan merasa kesulitan mengikuti ritme perkuliahan, sehingga terus menunda pengerjaan sampai deadline semakin dekat.
Akibatnya, proyek ini banyak bergantungan pada bantuan AI (claude).
Saya benar-benar menyesal telah menyia-nyiakan waktu yang saya punya. Hari ini saya berusaha menyelesaikannya semampu saya, tetapi saya sadar seharusnya saya mulai jauh lebih awal.
Saya minta  maaf atas kurangnya usaha saya dalam mengerjakan project uas ini dan berapa pun nilai diberikan, saya akan menerimanya dengan ikhlas karena saya tahu usaha saya memang belum cukup.
