# Deuda técnica — COMANDA

Inventario vivo de items pendientes. Cada entrada referencia el sprint
donde se detectó + sprint propuesto para resolverla. Ordenado por
impacto visible al usuario, no por facilidad.

---

## Single-branch housekeeping (Sprint 1.D propuesto)

El install viene de un vendor multi-tenant pero COMANDA opera como
single-branch. Hay trabajo pendiente de ocultar/traducir restos del
vendor que violan `FILOSOFIA.md §5`.

- **Admin sidebar/nav**: ocultar toda pantalla que diga "Sucursales / Branches".
  Hoy `/admin/branches` y `/admin/branches/create` siguen accesibles y el
  empty state del POS (cuando pos viewer no ve branches) dirige al CTA
  "+ Crear Nueva Sucursal".
- **Traducción `"Branches are immutable in single-branch installation mode"`**
  a es_AR. O mejor: la pantalla ni siquiera debería ser accesible en este
  modo. Middleware `ReadOnlyBranchMutations` ya bloquea POST/DELETE, falta
  esconder la UI.
- **Empty state del POS**: mensaje que dice "No se encontraron sucursales"
  cuando la config no encuentra register. Reemplazar por "Seleccioná una
  caja" o "Configurá una caja antes de operar".
- **Grep global de términos prohibidos** por `FILOSOFIA §5`: "Branch",
  "Sucursal", "Tenant", "Server", "Check". Reemplazar por equivalentes
  alineados con la jerga gastronómica AR.

Detectado en: **Sprint 2** (bug 2 reveló la superficie).
Workaround actual: `DemoArSeeder` asigna `branch_id=1` a todos los users
para que el POS no caiga en el empty state "no hay sucursales".

---

## Housekeeping POS (Sprint 2.5 propuesto)

- **Archivos Dialog huérfanos** del Sprint 2 sin imports activos:
  - `frontend/src/modules/pos/pages/admin/viewer/Pos/Dialogs/StartOrderDialog.vue`
  - `frontend/src/modules/pos/pages/admin/viewer/Pos/Dialogs/GuestCountDialog.vue`
  Preservados por si se revierte el Sprint 2. Eliminar después de 2-3
  sprints si no hubo regresión.
- **Huérfanos de sprints anteriores**: `ConfiguracionHub.vue`,
  `CobrosHub.vue`, `CocinaHub.vue`, `PersonalHub.vue`, `MarketingHub.vue`,
  `SalonHub.vue`, `Drawers/Orders/Index.vue`. Barrer en housekeeping.

---

## UI / visual (Sprint 3 propuesto)

- **Admin panel base 16px**: migrar cuando se refactorice el admin. Sprint
  1.B solo tocó el POS viewer — los hubs de Configuración/Clientes/Menú
  siguen con escala default 14px de Vuetify.
- **Fallback `@error` en VImg** para productos con foto rota. Hoy si
  `product.thumbnail` apunta a una URL 404, se ve el broken-image nativo
  del browser en vez de caer al placeholder con iniciales del Sprint 1.B.
- **Slider de hue con gradient visual** en el category form. Hoy muestra
  un número 0-360 (opaco para un dueño no-técnico). El preview tile
  dummy ya existe — falta el track del slider coloreado con el espectro.
- **Transición `grid-template-columns` 250ms** en `.pos-layout` para
  suavizar la transición home ↔ working (hoy es instantánea). Requiere
  verificación cross-browser porque no todos animan grid-template.
- **Drawers (TableViewer / CashMovement / Orders / Caja)**: migrar
  colores hardcoded (`#8e44ad`, `#16a085`, `#2980b9`, `#e0e0e0`, etc.) a
  tokens Vuetify. El Sprint 1.A tocó el POS viewer principal pero los
  drawers quedaron pendientes.
- **Dialogs/Rewards + Dialogs/AvailableGifts**: bordes `#ededed` hardcoded
  — features loyalty desactivadas para v2, migrar cuando se reactiven.

---

## Infraestructura / data

- **Breadcrumb global "Configuración > Grupo > Tab > Acción"**: skippeado
  en Sprint QA anterior. Requiere construir un componente wrapper porque
  la estructura flat de Configuración no lo expone vía `route.matched`.
  Posible path: parser de `route.name` string (`admin.configuracion.operacion.formas.create`
  → split por `.`) con ubicación debajo del topbar.
- **Deep drawers** (Drawers/* en POS): refactor de diseño drawer-by-drawer
  para consolidar layout + semantic colors. No es puramente un tema visual,
  también hay patrones de navegación que conviene unificar (ej: CashMovement
  y Caja tienen lógica muy similar).
- **`admin.branches.index` permission en super_admin**: hoy el rol no lo
  tiene, por eso `Branch::list()` devuelve vacío. El fix del Sprint 2 fue
  asignar `branch_id=1` al admin (workaround). Un fix más correcto sería
  decidir si super_admin debe tenerlo O si el PosViewerService debería
  usar `withOutGlobalBranchPermission()` para listar branches en
  single-branch mode.

---

## Backward compat

- **Rutas vendor standalone** (`/admin/users/create`, `/admin/payment-methods/create`,
  `/admin/printers/create`, etc.) siguen coexistiendo con las rutas
  anidadas nuevas. Funcionan ambas: el form vendor sin tabs, el anidado
  con tabs. Si el equipo decide forzar solo las anidadas, agregar
  redirects explícitos desde las vendor.
- **Namespace `Forkiva`** en backend (`app/Forkiva.php` + 30 archivos
  que lo referencian). Rename a `Comanda` es un refactor cross-módulo
  fuera del scope de cualquier sprint de UI. Trivia histórica: en el UI
  (frontend) no queda ni una referencia — ahí el rebrand está completo.
