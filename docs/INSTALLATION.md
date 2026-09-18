# OpenFinance Installation Manual

This guide covers local development, cPanel/shared hosting, Apache virtual hosts and verification.

## 1. Requirements

OpenFinance demo mode requires:

- PHP 8.0 or newer;
- PHP sessions enabled;
- a modern browser;
- write access to `apps/sacco-public/storage/demo/` if JSON persistence is desired.

It does **not** require Composer, npm, MySQL, PostgreSQL, Firebase, M-Pesa, an SMS gateway or an email provider.

## 2. Clone the repository

```bash
git clone https://github.com/wanjohi-11/OpenFinance.git
cd OpenFinance
```

Confirm PHP:

```bash
php -v
```

## 3. Configure the public app

```bash
cp apps/sacco-public/.env.example apps/sacco-public/.env
```

For local use:

```dotenv
APP_NAME="OpenFinance"
APP_URL=http://127.0.0.1:8080
APP_ENV=demo
APP_DEBUG=true
SYSTEM_URL=http://127.0.0.1:8081
CONTACT_EMAIL=demo@example.test
GITHUB_URL=https://github.com/wanjohi-11/OpenFinance
```

## 4. Configure demo-cache permissions

Linux/macOS:

```bash
chmod -R 775 apps/sacco-public/storage/demo
```

If the directory is not writable, the public app falls back to PHP session storage.

## 5. Run locally

Terminal 1:

```bash
php -S 127.0.0.1:8080 -t apps/sacco-public
```

Terminal 2:

```bash
php -S 127.0.0.1:8081 -t apps/sacco-system
```

Open:

- Public: `http://127.0.0.1:8080`
- System: `http://127.0.0.1:8081`

## 6. Public smoke test

1. Open the public home page.
2. Visit Membership, Savings, Loans and Resources.
3. Change the savings planner values.
4. Change the loan-estimator values.
5. Submit a synthetic membership application.
6. Copy the generated reference.
7. Test Application Status.
8. Also test the seeded record:
   - Reference: `OF-DEMO-001`
   - Email: `demo@example.com`
9. Submit a synthetic contact enquiry.
10. Verify `storage/demo/*.json` changes if the directory is writable.

## 7. System smoke test

1. Open the system URL.
2. Enter any display name.
3. Choose Administrator, Finance or Member Services.
4. Open the dashboard.
5. Visit Members, Contributions, Loans, Guarantors, Ledger, Receipts, Expenses, Communications and Support Tickets.
6. Add a synthetic record where available.
7. Refresh the page.
8. Confirm the new record remains.
9. Open Reports and verify totals update.
10. Select Reset demo data and confirm the original seed returns.

## 8. cPanel deployment

### Create domains

Create two subdomains with separate document roots:

```text
sacco.valron.co.ke
system-sacco.valron.co.ke
```

Example roots:

```text
/home/USERNAME/public_html/sacco/
/home/USERNAME/public_html/system-sacco/
```

### Deploy the public app

Upload the **contents** of `apps/sacco-public/` to the public subdomain root.

Correct:

```text
/public_html/sacco/index.php
/public_html/sacco/assets/app.css
/public_html/sacco/storage/demo/
```

Avoid an extra nested project directory.

### Configure public environment

Create `.env`:

```dotenv
APP_NAME="OpenFinance"
APP_URL=https://sacco.valron.co.ke
APP_ENV=demo
APP_DEBUG=false
SYSTEM_URL=https://system-sacco.valron.co.ke
CONTACT_EMAIL=demo@sacco.valron.co.ke
GITHUB_URL=https://github.com/wanjohi-11/OpenFinance
```

### Deploy the system app

Upload the **contents** of `apps/sacco-system/` to the system subdomain root.

Correct:

```text
/public_html/system-sacco/index.php
/public_html/system-sacco/assets/system.css
/public_html/system-sacco/assets/store.js
```

### Select PHP

Use MultiPHP Manager / Select PHP Version and assign PHP 8.0+ to both subdomains.

### Enable HTTPS

Issue SSL certificates for both domains with AutoSSL, Let's Encrypt or the provider's SSL feature.

### Verify permissions

The only directory that needs write access in demo mode is:

```text
apps/sacco-public/storage/demo/
```

Use `0755` or `0775` depending on hosting ownership. Avoid `0777` unless the hosting provider specifically requires it.

## 9. Apache virtual-host example

```apache
<VirtualHost *:80>
    ServerName sacco.openfinance.test
    DocumentRoot /path/to/OpenFinance/apps/sacco-public

    <Directory /path/to/OpenFinance/apps/sacco-public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName system-sacco.openfinance.test
    DocumentRoot /path/to/OpenFinance/apps/sacco-system

    <Directory /path/to/OpenFinance/apps/sacco-system>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## 10. Troubleshooting

### Blank page or HTTP 500

Check the PHP error log and PHP version. The demo expects PHP 8+ syntax.

### Public submissions do not update JSON

Check ownership and write permissions for `storage/demo/`. The app can still fall back to the current PHP session.

### System changes disappear

The operations system uses browser `localStorage`. Clearing site data, using private browsing, or switching browser profiles creates a fresh dataset.

### Demo login loops

Confirm PHP sessions are enabled and the PHP session save path is writable.

### CSS/JS does not load

Confirm you uploaded the **contents** of the app folder and that `assets/` sits beside `index.php`.

## 11. Production warning

Do not add a database connection and treat the result as a production core-banking system. Complete the controls in `docs/PRODUCTION-CHECKLIST.md`, including identity, authorization, transactional data design, auditability, reconciliation, security review and jurisdiction-specific regulatory analysis.
