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

- **Backend API**: `http://forkiva.test/api/v1` (vhost Apache + mod_php + opcache)
- **Frontend SPA dev**: `http://localhost:3101` (via `yarn dev`)

El backend corre detrás de un vhost Apache de Laragon (no `php artisan serve`)
para que las latencias sean viables en dev (~150-400ms vs 1s+ del dev server).
Ver "Operación local" más abajo.

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
# NO se usa "php artisan serve" - el backend vive detras del vhost
# Apache de Laragon en http://forkiva.test (ver "Operación local").

# Migraciones y seeders modulares (NO usar migrate plano)
php artisan module:migrate -a --force
php artisan module:seed -a --force

# Queue worker (los listeners criticos son ShouldQueueAfterCommit)
php artisan queue:work --tries=3 --backoff=10

# Scheduler (cleanup diario de idempotency_keys, otros)
php artisan schedule:work                   # foreground, dev
# En prod: cron cada minuto -> "php artisan schedule:run"

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

Cuando agregamos migrations propias (ej: CHECK constraints, `pase_uuid`, etc.) las ponemos bajo `Modules/<M>/database/migrations/<YYYY_MM_DD_HHMMSS>_<name>.php` con timestamp posterior a todas las del vendor. De esa forma un `module:migrate -a --force` las aplica en orden correcto y futuros updates del vendor no chocan (siempre dejamos nuestros timestamps en el futuro del último vendor).

### Convenciones propias (fuera del vendor)

- **`backend/app/Traits/HasPaseUuid.php`**: trait que agrega `pase_uuid` (CHAR(26) ULID) auto-generado en `creating` hook. Aplicado en `Order`, `Payment`, `Invoice`, `Product` via `use HasPaseUuid`. PASE (sistema externo) referencia los recursos por este ULID, nunca por el bigint id interno.
- **`backend/app/Http/Middleware/`**: middlewares propios:
  - `ReadOnlyBranchMutations`: bloquea POST/DELETE /branches (single-branch mode).
  - `ValidateSingleBranchInvariant`: asegura `branches.count()==1` en cada request HTTP.
  - `IdempotencyKey`: opt-in via header `X-Idempotency-Key`, cache de response 24h en tabla `idempotency_keys`.

### CHECK constraints branch_id=1

Las 28 tablas con `branch_id` tienen CHECK `(branch_id IS NULL OR branch_id = 1)` + FK con `ON DELETE RESTRICT`. Cualquier INSERT/UPDATE con otro branch_id falla a nivel DB. Defense in depth para el modo single-branch. Si algún día volvemos multi-branch real, hay que dropear estos constraints — pero el diseño actual asume 1 instalación = 1 sucursal física + PASE central multi-tenant.

## Operación local

### Apache vhost (backend)

`C:\laragon\etc\apache2\sites-enabled\00-forkiva.test.conf` apunta `forkiva.test` → `backend/public/`. El prefix `00-` fuerza precedencia sobre el vhost auto-generado de Laragon (renombrado a `.disabled`).

Reload Apache tras cambios en config:
```powershell
# desde PowerShell
Get-Process httpd | Stop-Process -Force
Start-Process -FilePath 'C:\laragon\bin\apache\httpd-2.4.66-260223-Win64-VS18\bin\httpd.exe' -ArgumentList '-d','C:/laragon/bin/apache/httpd-2.4.66-260223-Win64-VS18' -WindowStyle Hidden
```
O desde la GUI de Laragon: botón "Reload".

### Opcache

`C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini`:
- `zend_extension=opcache`
- `opcache.enable=1`, `enable_cli=0`
- `memory_consumption=256`, `max_accelerated_files=20000`
- `validate_timestamps=1`, `revalidate_freq=2` (dev: 2s de lag entre edit y efecto)

### CORS (dev)

`.env`: `CORS_ALLOWED_ORIGINS=http://localhost:3101,http://forkiva.test`.
Si arrancás Vite en otro puerto (3102, etc.), agregarlo al CSV.

### Cache

`.env`: `CACHE_ENABLED=true` en dev y en prod. La flag la lee `Forkiva::cacheEnabled()` y `CoreServiceProvider` la usa para decidir el driver (`file` vs `array`). Con `false` todas las llamadas `Cache::*` se vuelven no-op — la app sigue funcionando pero se pierden las optimizaciones de `Setting::allCached()`, `TranslationLoader::rememberForever()`, etc. No la flippes a `false` a menos que estés depurando un bug de cache específico; hoy no hay código que asuma `true` obligatoriamente (el sistema tolera ambos valores, solo difiere en perf).

### Queue worker

Los listeners críticos son `ShouldQueueAfterCommit` — sin worker corriendo, los jobs se acumulan en la tabla `jobs`. Para dev correr en terminal aparte:
```bash
cd backend && php artisan queue:work --tries=3 --backoff=10
```
