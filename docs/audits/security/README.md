# Security Audits

This directory contains security audit reports for the `simple_rest_api` extension.

## Purpose

- Document the security posture of the extension at a point in time
- Provide historical reference for how security issues were identified and resolved
- Enable the Security Expert agent to compare current state against past audits

## Naming Convention

```
YYYY-MM-DD-vX.Y.Z.md
```

Example: `2026-04-10-v0.2.4.md`

## When Audits Are Created

- Before every release (mandatory — see `security.md` release gate)
- On demand via the `/security-check` command
- After any significant change to middleware, routing, or parameter handling

## Index

| Date | Version | Status | Findings |
|---|---|---|---|
| 2026-04-10 | [v0.2.4](./2026-04-10-v0.2.4.md) | ⚠️ Passed with warnings | 3 Medium, 4 Low/Informational |
| 2026-04-10 | [v0.3.0](./2026-04-10-v0.3.0.md) | ✅ Passed | 0 findings (all v0.2.4 findings resolved) |
| 2026-04-16 | [v0.3.1](./2026-04-16-v0.3.1.md) | ✅ Passed | 0 findings (metadata release) |
