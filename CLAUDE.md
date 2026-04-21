# Forkiva

Sistema POS y gestión para restaurantes. Vendor: **Tenvoro** (CodeCanyon item 59812356).
Base comprada para adaptarse a restaurantes argentinos e integrarse con el proyecto PASE.

## Stack detectado

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | Laravel | 12.46.0 |
| PHP | — | 8.3.30 (Laragon) |
| Arquitectura modular | `nwidart/laravel-modules` | ^12.0 |
| PDF/Screenshots | `spatie/browsershot` + Puppeteer | ^5.1 / ^24 |
| Frontend | Vue 3 + Vuetify | 3.5 / 3.11 |
| Build tool | Vite | 7.x |
| TypeScript | — | 5.9 |
| Package manager (frontend) | Yarn | 1.22.22 |
| Node | — | 24.14.1 |
| Base de datos | MySQL | 8.4.3 (Laragon) |

## URLs locales

- **Backend API**: `http://localhost:8000/api/v1` (via `php artisan serve`)
- **Frontend SPA dev**: `http://localhost:3101` (via `yarn dev`)
- **NO** se usa `http://forkiva.test` — el proyecto no es un vhost Laragon clásico porque el frontend es un SPA separado que consume la API.

## Credenciales

### Base de datos
- Host: `127.0.0.1:3306`
- Database: `forkiva`
- User: `root`
- Password: *(vacío)*

### Admin default (sembrado por seeders)
- Email: `admin@forkiva.app`
- Username: `admin`
- Password: `12345678`
- Role: `super_admin`

Datos demo cargados: 11 usuarios, 8 roles (super_admin / admin / admin_branch / manager / cashier / kitchen / waiter / customer), 3 branches, programas de loyalty y gift cards.

## Estructura

```
forkiva/
├── backend/              Laravel 12 API (nwidart/laravel-modules, 31 módulos)
│   ├── Modules/          Módulos del vendor (Core, Pos, Order, Hr no viene, etc.)
│   ├── app/              Código base Laravel
│   └── .env              Local (ignorado por git)
├── frontend/             Vue 3 + Vuetify 3 + Vite 7 (SPA)
│   └── src/              Código frontend
├── Documentation/        Docs oficiales del vendor (VitePress)
├── Forkiva.postman_collection.json
└── CLAUDE.md             Este archivo
```

**El módulo Hr (Human Resources) es addon pago separado — no viene en la compra base.**

## Comandos usuales

### Backend (correr desde `backend/`)

```bash
# Levantar dev server
php artisan serve                           # :8000

# Migraciones y seeders modulares (NO usar migrate plano)
php artisan module:migrate -a --force
php artisan module:seed -a --force

# Listar módulos
php artisan module:list

# Rutas
php artisan route:list --path=api

# Regenerar autoload (tras tocar Modules/)
composer dump-autoload --optimize

# Tests (si se agregan)
php artisan test
```

### Frontend (correr desde `frontend/`)

```bash
# Instalar deps (usar YARN, no npm — el vendor ya trae yarn.lock)
yarn install

# Dev server con HMR
yarn dev                                    # :3101

# Build producción (genera dist/ + PWA con service worker)
yarn build

# Type-check solo
yarn type-check

# Lint (oxlint + eslint)
yarn lint
```

### DB helpers

```bash
# Recrear DB (desde cero)
mysql -u root -e "DROP DATABASE IF EXISTS forkiva; CREATE DATABASE forkiva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cd backend && php artisan module:migrate -a --force && php artisan module:seed -a --force
```

## Git / vendor baseline

- Repo remoto: `https://github.com/lucastomasferrari-cell/COMANDA.git`
- Branch principal: `main`
- Tag **`vendor-baseline`**: apunta al commit inicial con todo el código del vendor tal como vino (más un pequeño fix de enum faltante, ver abajo). **Nunca pushear a `main` sin revisar** — si necesitás volver al estado vendor para comparar, `git diff vendor-baseline`.

### Fix aplicado sobre el baseline del vendor

El zip del vendor tenía un bug: `Modules/Printer/app/Enum/PrinterConnectionType.php` estaba referenciado por 3 migraciones (create, change, drop) pero el archivo no venía en el zip. Se creó el stub con casos `Tcp`/`Usb`/`Bluetooth`. La migración final dropea la columna igual, así que el enum queda como trivia histórica.

## Configuración del ambiente Windows

Se habilitó `extension=zip` en `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini` (venía comentado por default en Laragon y composer lo requiere por `phpoffice/phpspreadsheet`).

Extensiones PHP necesarias (todas ya activas): `bcmath`, `gd`, `iconv`, `intl`, `mbstring`, `curl`, `openssl`, `pdo_mysql`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `dom`, `filter`, `hash`, `session`, `sodium`, `zip`.

## Contexto futuro

### Adaptación Argentina
El objetivo a mediano plazo es adaptar este sistema para restaurantes **argentinos**. Cambios previstos:

- **Facturación AFIP**: integración con web services (WSFE, WSFEv1) para emitir facturas A/B/C/M electrónicas con CAE.
- **IVA**: tasas 21% / 10.5% / 27% / 0% (el vendor maneja tax genérico; hay que mapear).
- **Identificación fiscal**: CUIT / CUIL / DNI en clientes y proveedores (el vendor usa `vat_tin` genérico).
- **Métodos de pago locales**: **Mercado Pago** (Point / Checkout Pro), **Modo**, transferencia, débito/crédito con cuotas.
- **Propina**: cálculo y distribución (no nativo en el vendor).
- **Moneda**: ARS como default, pero manejar multi-moneda por inflación/dólar.
- **Localización**: `es_AR`, formato de fecha `dd/mm/YYYY`, separadores decimales.

### Integración con PASE
PASE es un proyecto hermano (React + TS + Supabase + Vercel).

- Supabase URL: `pduxydviqiaxfqnshhdc.supabase.co`
- Integración planificada: **webhook** desde Forkiva → PASE para sincronizar pedidos/pagos/clientes.
- Cuando se implemente, documentar el payload acá y en el endpoint correspondiente.

## Convenciones

### Monorepo
Este repo es un **monorepo** con dos proyectos independientes que se comunican por HTTP:
- `backend/` → Laravel 12 API-only, corre en `:8000`
- `frontend/` → Vue 3 SPA consumidor de la API, corre en `:3101`

No mezclar: el frontend **no** se compila dentro de `backend/public/`. Cada lado tiene su propio `package.json`, `.env`, lockfile y build. Cambios típicamente afectan un solo lado; cuando afectan ambos (ej: agregar un endpoint + consumirlo), conviene un solo PR con commits separados por capa.

### Commits
Prefijos convencionales: `chore:`, `feat:`, `fix:`, `docs:`, `refactor:`, `test:`.
Se puede prefijar el scope: `feat(backend):`, `fix(frontend):`, `docs(claude):`.

### Migraciones
Usar **siempre** `php artisan module:migrate` — el vendor no usa la tabla `migrations` plana de Laravel sino el tracker de `nwidart/laravel-modules`. Misma lógica para seeders con `module:seed`.
