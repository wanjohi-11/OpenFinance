# OpenFinance Public SACCO Demo

Intended demo URL: `https://sacco.valron.co.ke`

## Run

```bash
cp .env.example .env
php -S 127.0.0.1:8080
```

The application does not require a database.

### Demo persistence

- applications/enquiries are written to `storage/demo/*.json` when writable;
- otherwise the current PHP session is used;
- no payment, email, SMS, Firebase, KYC or other production API is contacted.

### Seeded status lookup

- Reference: `OF-DEMO-001`
- Email: `demo@example.com`

Use synthetic data only.
