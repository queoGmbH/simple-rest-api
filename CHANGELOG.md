# Changelog

All notable changes are documented here. For full details see
[Documentation/Changelog/Index.rst](Documentation/Changelog/Index.rst).

## [0.5.0] — 2026-04-23

### Added
- SQL fixture dumps for E2E tests (TYPO3 13 and 14), enabling reproducible end-to-end test runs
- `debugMode` configuration option documented in Configuration reference

### Documentation
- Corrected middleware key, cache config key typo, base path validation pattern
- Corrected `AsApiEndpoint::TAG_NAME`, `ApiEndpoint` constructor, `EndpointListController` signature
- Fixed `BeforeParameterMappingEvent` API reference and event listener examples
- Fixed Developer Guide: `ddev start` before `ddev composer install`, `ddev exec` prefixes
- Moved `FilteringEventListeners` page from Developer to Events section

## [0.4.0] — 2026-04-17

### Added
- TYPO3 14.0 compatibility — `composer.json` widened to `^13.4 || ^14.0`, `ext_emconf.php` updated to `13.4.0-14.99.99`
- PHP 8.5 compatibility — CI pipeline extended; no code changes required
- Full CI matrix: PHP 8.2 / 8.3 / 8.4 / 8.5 × TYPO3 13 / 14
- Icon registration moved to `Configuration/Icons.php` (TYPO3 12+ convention, fixes TYPO3 14 breaking change)

### Changed
- `b13/make` removed from require-dev

## [0.3.1] — 2026-04-16

### Fixed
- Cache key typo `somple_rest_api` → `simple_rest_api` in `ext_localconf.php`
- `Documentation/Settings.cfg` version corrected to `0.3.1`

### Added
- `ext_emconf.php` for TYPO3 Extension Repository (TER) compatibility
- `LICENSE` file (GPL-2.0-or-later)
- `Resources/Public/Icons/Extension.svg` for TER listing
- `SECURITY.md` with vulnerability reporting policy
- `CHANGELOG.md` in repository root
- `composer.json` metadata: `authors`, `keywords`, `homepage`, `support`

## [0.3.0] — 2026-04-16

### Security
- Security logging added to `ApiResolverMiddleware` for unmatched API paths (M-01)
- Security logging on parameter coercion failure before returning 400 (M-02)
- Validated type coercion via `filter_var()` — invalid values now return 400 (M-03)
- Security notice added to `AsApiEndpoint` attribute PHPDoc (L-01)
- `CacheHashFixer` now restores `TYPO3_CONF_VARS` in `finally` block (L-02)
- `error_log()` removed from example event listener (L-03)

### Added
- `InvalidParameterException` for parameter coercion failures
- `LoggerInterface` constructor injection in `ApiResolverMiddleware`
- `psr/log: ^3.0` as explicit Composer dependency

## [0.2.4] — 2026-03-23

### Fixed
- `CacheHashFixer` now uses dynamic API base path from extension configuration
- `CacheHashFixer` now respects language base path in multi-language TYPO3 sites

## [0.2.3] — 2026-01-16

### Documentation
- Complete changelog entries for versions 0.2.1 and 0.2.2

## [0.2.2] — 2025-12-09

### Added
- Debug mode configuration to control visibility of internal endpoints

## [0.2.1] — 2025-12-09

### Fixed
- Language-specific base URI support for multi-language TYPO3 setups

### Added
- Comprehensive integration tests for all HTTP methods

## [0.2.0] — 2025-11-03

### Added
- Configurable API base path via TYPO3 Site Set Settings
- `ModifyApiResponseEvent` for response modifications
- Backend module to list all registered API endpoints
- Color-coded HTTP method badges in backend module

## [0.1.0] — 2025-01-15

Initial release with core functionality: `AsApiEndpoint` attribute, URL parameter mapping,
middleware stack, PSR-14 events, and TYPO3 route enhancer.
