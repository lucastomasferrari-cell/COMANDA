-- cleanup-demo-data.sql
--
-- Borra TODOS los datos demo sembrados por los seeders del vendor
-- para dejar una instalación limpia lista para que un cliente real
-- la configure con sus propios datos (branches, menú, productos, etc.).
--
-- Se mantiene:
--   * admin user (id=1, username='admin', email='admin@forkiva.app')
--     con su rol super_admin.
--   * roles (8 roles estructurales: super_admin, admin, admin_branch,
--     manager, cashier, kitchen, waiter, customer).
--   * permissions (catálogo completo del sistema).
--   * role_has_permissions (mapa de permisos por rol).
--   * settings (configuración Comanda: nombres, colores, branding).
--   * translations (si existen — referencia multilenguaje).
--   * migrations (tracking interno de Laravel).
--
-- Uso:
--   mysql -u root forkiva < backend/database/scripts/cleanup-demo-data.sql
--
-- NOTA: después de correr este script, el admin va a tener que
-- re-loguearse (se truncan los personal_access_tokens). También
-- hay que crear al menos una branch antes de poder vender desde
-- el POS.

SET FOREIGN_KEY_CHECKS = 0;

-- Transaccional de ventas
TRUNCATE TABLE orders;
TRUNCATE TABLE order_products;
TRUNCATE TABLE order_product_options;
TRUNCATE TABLE order_product_option_values;
TRUNCATE TABLE order_taxes;
TRUNCATE TABLE order_discounts;
TRUNCATE TABLE order_status_logs;
TRUNCATE TABLE payments;
TRUNCATE TABLE payment_allocations;
TRUNCATE TABLE invoices;
TRUNCATE TABLE invoice_lines;
TRUNCATE TABLE invoice_taxes;
TRUNCATE TABLE invoice_discounts;
TRUNCATE TABLE invoice_parties;
TRUNCATE TABLE invoice_counters;

-- POS
TRUNCATE TABLE pos_sessions;
TRUNCATE TABLE pos_cash_movements;
TRUNCATE TABLE pos_registers;
TRUNCATE TABLE carts;

-- Inventario
TRUNCATE TABLE stock_movements;
TRUNCATE TABLE purchase_items;
TRUNCATE TABLE purchases;
TRUNCATE TABLE purchase_receipt_items;
TRUNCATE TABLE purchase_receipts;

-- Plano del salón
TRUNCATE TABLE table_status_logs;
TRUNCATE TABLE table_merge_members;
TRUNCATE TABLE merge_snapshots;
TRUNCATE TABLE table_merges;
TRUNCATE TABLE tables;
TRUNCATE TABLE zones;
TRUNCATE TABLE floors;

-- Catálogo de productos
TRUNCATE TABLE ingredientables;
TRUNCATE TABLE product_categories;
TRUNCATE TABLE product_options;
TRUNCATE TABLE product_taxes;
TRUNCATE TABLE products;
TRUNCATE TABLE categories;

-- Menús
TRUNCATE TABLE online_menus;
TRUNCATE TABLE menus;

-- Opciones / modifiers
TRUNCATE TABLE option_values;
TRUNCATE TABLE options;

-- Inventario base
TRUNCATE TABLE ingredients;
TRUNCATE TABLE units;
TRUNCATE TABLE suppliers;

-- Promociones
TRUNCATE TABLE discounts;
TRUNCATE TABLE vouchers;
TRUNCATE TABLE reasons;

-- Gift cards
TRUNCATE TABLE gift_card_transactions;
TRUNCATE TABLE gift_cards;
TRUNCATE TABLE gift_card_batches;

-- Loyalty
TRUNCATE TABLE loyalty_gifts;
TRUNCATE TABLE loyalty_transactions;
TRUNCATE TABLE loyalty_customers;
TRUNCATE TABLE loyalty_rewards;
TRUNCATE TABLE loyalty_promotions;
TRUNCATE TABLE loyalty_tiers;
TRUNCATE TABLE loyalty_programs;

-- Impuestos y monedas
TRUNCATE TABLE taxes;
TRUNCATE TABLE currency_rates;

-- Impresoras
TRUNCATE TABLE print_agents;
TRUNCATE TABLE printers;

-- Media
TRUNCATE TABLE model_files;
TRUNCATE TABLE media;

-- Branches
TRUNCATE TABLE branches;

-- Logs y transient
TRUNCATE TABLE activity_log;
TRUNCATE TABLE authentication_log;
TRUNCATE TABLE personal_access_tokens;
TRUNCATE TABLE password_reset_tokens;
TRUNCATE TABLE sessions;
TRUNCATE TABLE jobs;
TRUNCATE TABLE job_batches;
TRUNCATE TABLE failed_jobs;

-- Usuarios: preservar solo admin (id=1) y su rol
DELETE FROM model_has_permissions WHERE model_id != 1;
DELETE FROM model_has_roles WHERE model_id != 1;
DELETE FROM users WHERE id != 1;

SET FOREIGN_KEY_CHECKS = 1;
