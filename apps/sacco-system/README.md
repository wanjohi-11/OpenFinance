# OpenFinance SACCO System Demo

Intended demo URL: `https://system-sacco.valron.co.ke`

## Run

```bash
php -S 127.0.0.1:8081
```

Open `http://127.0.0.1:8081` and enter the passwordless demo workspace.

## Persistence

- PHP session: demo sign-in only;
- browser `localStorage`: synthetic members, contributions, loans, ledger, receipts, expenses, communications and support tickets;
- Reset demo data: restores the seed dataset.

No MySQL, Firebase, M-Pesa, card, SMS, email or KYC API is required or called.

This is not a production core-banking system.
