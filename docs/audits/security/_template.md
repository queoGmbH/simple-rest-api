# Security Audit — vX.Y.Z

**Date:** YYYY-MM-DD
**Version:** X.Y.Z
**Auditor:** Security Expert Agent
**Triggered by:** Release gate | `/security-check` command | Manual
**Scope:** Full extension audit | Changed files only

---

## Summary

| Category | Status | Findings |
|---|---|---|
| A01 Broken Access Control | ✅ / ⚠️ / ❌ | N findings |
| A03 Injection | ✅ / ⚠️ / ❌ | N findings |
| A04 Insecure Design | ✅ / ⚠️ / ❌ | N findings |
| A05 Security Misconfiguration | ✅ / ⚠️ / ❌ | N findings |
| A06 Vulnerable Components | ✅ / ⚠️ / ❌ | N findings |
| A09 Security Logging | ✅ / ⚠️ / ❌ | N findings |
| Dependencies | ✅ / ⚠️ / ❌ | N findings |

**Overall status:** ✅ Passed | ⚠️ Passed with warnings | ❌ Failed — release blocked

---

## Findings

### Critical (release blocking)

*(none | list findings)*

### High

*(none | list findings)*

### Medium

*(none | list findings)*

### Low / Informational

*(none | list findings)*

---

## OWASP Checklist

### A01 — Broken Access Control
- [ ] No endpoint is accessible without the consumer implementing authentication
- [ ] Consumer security responsibilities are documented in the extension docs
- [ ] No TYPO3 page access restrictions are inadvertently bypassed

**Notes:** ...

### A03 — Injection
- [ ] All URL parameters are type-coerced before being passed to consumer methods
- [ ] No raw URL parameter values are used in TYPO3 queries or system calls within the extension
- [ ] `InvalidParameterException` (400) is thrown on coercion failure

**Notes:** ...

### A04 — Insecure Design
- [ ] Extension does not handle auth, authz, or rate limiting (by design — documented)
- [ ] Consumer responsibility documentation is present and up to date
- [ ] No new features implicitly assume security guarantees not provided by the extension

**Notes:** ...

### A05 — Security Misconfiguration
- [ ] Base path validation enforces correct format (`/name/`)
- [ ] Route enhancer configuration is validated
- [ ] Default configuration exposes minimum necessary surface

**Notes:** ...

### A06 — Vulnerable Components
- [ ] `composer audit` output: no known vulnerabilities
- [ ] All dependencies are justified and minimized
- [ ] No new unjustified dependencies added since last audit

**Notes:** ...

### A09 — Security Logging
- [ ] Invalid parameter type triggers a WARNING log entry
- [ ] Endpoint not found triggers a WARNING log entry
- [ ] Log channel `simple_rest_api` is used consistently

**Notes:** ...

---

## Dependency Scan

```
# Output of: composer audit
(paste output here)
```

---

## Comparison with Previous Audit

**Previous audit:** YYYY-MM-DD vX.Y.Z
**New findings since last audit:** ...
**Resolved findings since last audit:** ...

---

## Recommendations

*(List any improvements recommended for future releases)*
