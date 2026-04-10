# Architecture Decision Records

This directory contains Architecture Decision Records (ADRs) for the simple_rest_api extension.

An ADR documents a significant architectural decision: the context that led to it, the decision itself, the reasoning behind it, and the consequences — both positive and negative.

## When to write an ADR

- When a new architectural pattern or approach is introduced
- When a significant trade-off is made (e.g. simplicity vs. flexibility)
- When a decision affects the public API or extension consumers
- When a decision could reasonably be questioned or revisited in the future
- When the Architect agent proposes a new structural approach

## Format

Files are named `NNNN-short-title.md` (e.g. `0001-use-php-attributes-for-endpoint-registration.md`).

Use the template in `_template.md`.

## Status values

- **Proposed** — under discussion, not yet implemented
- **Accepted** — decision is in effect
- **Deprecated** — no longer recommended, but not yet replaced
- **Superseded by ADR-NNNN** — replaced by a newer decision

## Index

| ADR | Title | Status |
|---|---|---|
| [0001](0001-use-php-attributes-for-endpoint-registration.md) | Use PHP Attributes for Endpoint Registration | Accepted |
| [0002](0002-middleware-based-routing.md) | Middleware-Based Routing Approach | Accepted |
| [0003](0003-psr14-events-for-extensibility.md) | PSR-14 Events for Extensibility | Accepted |
| [0004](0004-configurable-base-path-via-site-settings.md) | Configurable Base Path via TYPO3 Site Settings | Accepted |
| [0005](0005-no-builtin-auth-or-serialization.md) | No Built-in Authentication or Serialization | Accepted |
| [0006](0006-internal-annotation-for-public-api-boundary.md) | @internal Annotation for Public API Boundary | Accepted |
