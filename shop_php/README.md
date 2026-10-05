# SHOP & TUFF — PHP + MySQL

Converted from the React/Vite project and extended into a full shop:
storefront, product pages, cart, accounts, checkout, order history and an admin panel.

## Install (XAMPP)
1. Copy the `shop_php` folder to `C:\xampp\htdocs\`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel
3. Open http://localhost/phpmyadmin > **Import** and choose:
   - `database.sql` for a fresh install (drops and recreates the tables), or
   - `upgrade.sql` if you already imported the previous version and want to keep your data
4. Open http://localhost/shop_php/
5. Register an account on the site, then make it an admin in phpMyAdmin (SQL tab):
   `UPDATE users SET role = 'admin' WHERE email = 'you@example.com';`
   An **Admin panel** link then appears in your account menu (or open `/shop_php/admin/`).

DB settings are in `includes/db.php` (default: root, empty password).

## Pages
| URL | What it does |
|-----|--------------|
| `index.php` | Home, categories, search, sorting |
| `product.php?id=` | Product page with quantity and related items |
| cart drawer | Opens from any page (`?cart=1`), add / change / remove |
| `login.php`, `register.php` | Accounts (passwords hashed with `password_hash`) |
| `checkout.php` | Login required; order saved in `orders` + `order_items` |
| `orders.php` | The customer's order history and details |
| `settings.php` | Profile, password, delete account |
| `admin/` | Dashboard, products (add / edit / hide / delete / upload image), orders (update status), customers (roles) |

## Security notes
- All forms use CSRF tokens; all SQL uses prepared statements; output is escaped.
- Card details are validated only and never stored. Use a payment provider (Stripe, PayPal…) for real payments.
- Uploads go to `assets/uploads/` (JPG/PNG/WebP, max 2 MB, scripts blocked by `.htaccess`).
- Notification switches in Settings are kept per session only.
- Before going live: set a MySQL password, use HTTPS, and hide PHP errors (`display_errors = Off`).

## Files
```
includes/   functions.php, db.php, header/footer, cart drawer, admin layout
admin/      dashboard, products, product form, orders, order, users
assets/     css/app.css (design system), img/, uploads/
```
