# Production Readiness Checklist

OpenFinance is not production-ready. Before using a fork for real financial operations, complete a formal engineering, security and regulatory process.

## Identity
- secure authentication;
- MFA where appropriate;
- least-privilege RBAC;
- session expiry and revocation.

## Data
- transactional relational model;
- integrity constraints and indexes;
- concurrency controls and idempotency;
- tested backups and restore procedures.

## Financial controls
- authoritative balance rules;
- reconciliation;
- duplicate-posting prevention;
- verified payment webhooks/callbacks;
- immutable or tamper-evident audit trails.

## Security
- HTTPS;
- protected server-side secrets;
- CSRF protection;
- server-side validation;
- rate limiting;
- secure cookies;
- SAST/dependency/secrets scans;
- independent security assessment.

## Privacy and compliance
- jurisdiction-specific licensing analysis;
- KYC/AML controls where applicable;
- lawful processing and retention;
- access logs;
- incident/breach procedures.

## Operations
- monitoring and alerting;
- disaster recovery;
- staging/UAT;
- automated tests;
- controlled deployment pipelines.
