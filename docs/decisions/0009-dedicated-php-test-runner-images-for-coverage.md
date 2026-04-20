# 0009 — Dedicated PHP Test Runner Images for Coverage

**Status:** Accepted
**Date:** 2026-04-20
**Author:** Sebastian Hofer

## Context

The project has no PHP coverage driver available in its ddev container (pcov is not installable
in the current setup; Xdebug is not enabled for CLI). The existing CI image `kanti/buildy` also
lacks pcov. Without a coverage driver, PHPUnit cannot produce coverage reports either locally or
in CI, which means GitLab's coverage badge and Cobertura visualisation cannot be used.

Adding pcov to the ddev container was evaluated but rejected because it requires modifying
the ddev configuration, which is shared across all developers and should remain minimal and
stable. Modifying `kanti/buildy` is not feasible because it is an externally maintained image.

## Decision

A dedicated, project-owned Docker image — the "test runner image" — is introduced solely for
running PHPUnit with coverage. It is separate from the existing `kanti/buildy` image used for
grumphp and standard unit test jobs, which remain untouched.

### Image design

- **Base:** `php:${PHP_VERSION}-cli` (official Docker Hub image)
- **Added:** pcov via `pecl install pcov && docker-php-ext-enable pcov`
- **Added:** composer, git, unzip (required for `composer install`)
- **Not baked in:** project Composer dependencies — these are installed at job runtime via
  `composer install` in `before_script`, benefiting from the existing per-PHP-version
  Composer cache

This keeps the image small and avoids rebuilding it on every `composer.json` change.

### Dockerfile

A single `Dev/Dockerfile.test-runner` with an `ARG PHP_VERSION` is used. It is built once
per PHP version and tagged accordingly.

### Registry

Images are pushed to the GitLab Container Registry via CI (`$CI_REGISTRY_IMAGE`) and pulled
using the public-facing Docker Hub proxy hostname:

```
dockerhub.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/test-runner:8.2
dockerhub.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/test-runner:8.3
dockerhub.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/test-runner:8.4
dockerhub.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/test-runner:8.5
```

The registry is the single source of truth. CI pulls from there; developers pull from there
after a one-time `docker login dockerhub.cloud.queo.org`. This guarantees local/CI parity.

### Rebuild policy

Images are **not** rebuilt on every push. The `build-test-runner-*` CI jobs run only when:

1. `Dev/Dockerfile.test-runner` changes on `main`
2. A scheduled pipeline runs (weekly, for security patch rebuilds)

Feature branch pipelines pull the last image published from `main`. If no image has been
published yet (e.g. fresh project setup), the coverage job will fail with a pull error. In that
case, one of the `build-test-runner-8.2/8.3/8.4` jobs must be triggered manually from
`main` first.

### Coverage job scope

The `coverage` job runs in the `test` stage and uses the PHP 8.4 image. Coverage numbers are
collected for a single PHP version because coverage variance across PHP versions is negligible
for this extension's codebase. The job produces:

- Cobertura XML report (`--coverage-cobertura`) for GitLab's coverage visualisation
- A coverage percentage extracted for the GitLab coverage badge via `coverage:` regex

Existing `kanti/buildy`-based jobs (grumphp, unit tests) are not changed.

### Local developer workflow

```bash
# One-time registry login
docker login registry.gitlab.cloud.queo.org

# Run coverage locally (integration + unit, matches CI)
bash Dev/coverage.sh
```

`Dev/coverage.sh` runs both integration and unit suites in the same order as the CI `coverage`
job, using the PHP 8.4 test runner image. It must NOT be replaced with `ddev exec phpunit
--coverage-*` because ddev's PHP runtime lacks pcov; doing so would silently produce reports
without actual coverage data.

## Reasoning

Owning a thin, purpose-built image gives full control over the coverage driver without
affecting the ddev environment or the externally maintained `kanti/buildy` image. The
GitLab Container Registry is the natural choice for a GitLab-hosted project because
authentication in CI is handled via `CI_JOB_TOKEN` without additional secrets.

The decision to limit coverage collection to PHP 8.4 reduces CI cost while producing
actionable coverage data. A full matrix coverage run (all PHP × TYPO3 version combinations)
would increase pipeline duration significantly for no meaningful gain in coverage information.

### Alternatives considered

| Alternative | Why rejected |
|---|---|
| Enable pcov in ddev | Requires modifying the shared ddev configuration, increasing maintenance surface and affecting all developers' environments. |
| Enable Xdebug for CLI in ddev | Xdebug's coverage mode has significant runtime overhead; still requires ddev config changes; creates a divergence between local and CI environments. |
| Patch `kanti/buildy` | The image is externally maintained; forking it creates a long-term maintenance burden and diverges from the upstream image's intended use. |
| Run coverage in the Playwright/E2E setup | The E2E setup uses a mariadb service and is designed for end-to-end HTTP testing. It is the wrong layer for unit/integration coverage. |
| Use a GitHub Actions runner with pcov | The project is GitLab-primary; GitHub is a mirror. Running authoritative CI on GitHub Actions would invert the source-of-truth relationship. |
| Collect coverage in all PHP matrix jobs | Increases pipeline duration and registry storage without meaningful additional information, since coverage numbers do not vary significantly across PHP versions for this codebase. |

## Consequences

### Positive
- Coverage reports (Cobertura + badge) are available in GitLab CI without touching ddev or
  existing CI jobs.
- Local and CI environments use the identical image, so locally produced coverage numbers
  match CI exactly.
- The thin image rarely needs rebuilding — only when the Dockerfile changes or on a weekly
  schedule for security patches.
- Existing grumphp and unit test jobs are entirely unaffected.

### Negative / Trade-offs
- A new image must be built and pushed to the registry before the coverage job can run for
  the first time. There is no graceful fallback if the image is absent from the registry.
- Developers must perform a one-time `docker login` to the GitLab registry to run coverage
  locally. This is a minor friction point for new contributors.
- PHP 8.5 coverage is not available until an official `php:8.5-cli` Docker Hub image ships.
- The `Dev/Dockerfile.test-runner` is a new file that contributors must be aware of when
  diagnosing CI failures related to coverage.
- Authentication in the `build-test-runner-*` CI jobs relies on `CI_JOB_TOKEN` (the standard
  GitLab ephemeral token for registry access). If the project's GitLab settings restrict
  job token scope, a project-level CI/CD variable may be required instead.

### Implications for consumers
- None. This decision affects only the development and CI infrastructure of the extension
  itself. No public API, configuration, or runtime behaviour is changed.
