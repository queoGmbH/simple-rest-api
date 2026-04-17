# 0008 — Dev Directory for E2E Test Fixture Controllers

**Status:** Accepted
**Date:** 2026-04-17
**Author:** Sebastian Hofer

## Context

Playwright E2E tests require stable, predictable HTTP endpoints to exercise the extension's
routing and parameter-handling behaviour end-to-end. These endpoints cannot live in the
production class tree (`Classes/`) because they would be registered in every TYPO3 environment,
including production, where exposing raw debug/test endpoints is a security risk and an
unnecessary public surface.

At the same time, the Symfony DI container only auto-discovers classes through the namespace
paths declared in `Configuration/Services.php`. For a fixture controller to receive proper DI
wiring (autowiring, autoconfiguration, and the `AsApiEndpoint` tag processor), it must be
reachable from that file. Placing fixture classes inside `Tests/` satisfies the isolation goal
but takes them outside the autoloading path used by `Services.php`, making DI wiring awkward.

A top-level `Dev/` directory already existed in the project to hold development-only tooling
(`RectorSettings`, `VersionUtility`). Extending that directory to also hold fixture controllers
keeps all development-time-only PHP in one place and requires only a single conditional block
in `Services.php`.

## Decision

Fixture controllers for E2E tests are placed in `Dev/Controller/` under the namespace
`Queo\SimpleRestApi\Dev\Controller\`. They are loaded into the Symfony DI container exclusively
when TYPO3 runs in Development context, via a runtime guard in `Configuration/Services.php`:

```php
if (Environment::getContext()->isDevelopment()) {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->load('Queo\\SimpleRestApi\\Dev\\', '../Dev/');
}
```

PHPStan (level 9) and the GrumPHP PHPStan task exclude `Dev/` via `ignore_patterns`.
Rector processes `Dev/` files because they are tracked by git and `rector.php` uses
`git ls-files` for path discovery; no explicit skip for `Dev/` has been added to
`RectorSettings::skip()`.

## Reasoning

Placing fixture controllers in `Dev/` with a runtime context guard satisfies three constraints
simultaneously: the controllers receive full DI wiring, they are guaranteed absent from
production containers, and they remain co-located with the other development-only utilities
that already live in `Dev/`.

The context check (`Environment::getContext()->isDevelopment()`) is evaluated at container
compile time, not at request time, so there is no runtime overhead in production.

### Alternatives considered

| Alternative | Why rejected |
|---|---|
| `Configuration/Services.dev.yaml` | TYPO3's conventional approach for context-specific services. Auto-loaded in Development context without an explicit guard. Rejected because it would require a separate file whose existence and purpose is less visible than an inline guard, and TYPO3 13 does not guarantee auto-loading of `Services.dev.yaml` in all configurations without additional setup. |
| `Tests/Fixtures/` directory | Keeps fixture code inside the test tree, which is semantically clean. Rejected because `Tests/` is not included in the DI autoload paths in `Services.php`; wiring fixture controllers from there would require either duplicating DI configuration or changing the autoload paths in a way that risks accidentally loading test classes in CI configurations that do not set a Development context. |
| Conditional loading via env var | More explicit than a context check and easier to control in CI. Rejected because it introduces a non-standard mechanism that is not idiomatic TYPO3, and the Development context is already the established convention for this extension's local and CI E2E setups. |
| Moving fixture controllers into `Classes/` with a guard tag | Would place test-only code in the production namespace, polluting the public API surface and making it harder to audit what constitutes the real extension API. |

## Consequences

### Positive
- Fixture controllers receive full Symfony DI wiring (autowiring, autoconfiguration,
  `AsApiEndpoint` tag) without any special-casing outside `Services.php`.
- Test endpoints are structurally impossible to load in Production or Staging TYPO3 contexts.
- All development-only PHP (`RectorSettings`, `VersionUtility`, fixture controllers) is
  co-located in `Dev/`, making the boundary between production and development code visible
  at a glance.
- PHPStan level-9 checks do not apply to `Dev/`, so fixture controllers are not held to
  production-grade static analysis.

### Negative / Trade-offs
- The `Dev/` directory and its namespace are shipped in the distributed Composer package.
  Consumers who install the extension will have these files on disk, even though they are
  never loaded outside Development context. This is a minor packaging concern.
- The runtime guard is a non-standard TYPO3 pattern; contributors unfamiliar with the project
  may not immediately understand why `Services.php` contains a conditional. The inline comment
  and this ADR mitigate the discoverability risk.
- Rector processes `Dev/` files (since they are git-tracked and `rector.php` uses
  `git ls-files`). This means Rector may propose refactorings to fixture code, which is
  mildly surprising given the intent to keep `Dev/` low-maintenance.
- Adding new fixture controllers for future E2E test scenarios requires placing them in
  `Dev/Controller/` and ensuring the TYPO3 context is set to Development in the relevant
  environment — this constraint must be communicated to contributors.

### Implications for consumers
- Consumers installing `queo/simple-rest-api` via Composer will receive the `Dev/` directory.
  The classes inside it are inert in any non-Development TYPO3 context.
- No public API is affected; `Dev/` is not part of the extension's public contract.
