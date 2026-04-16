# Changelog

All notable changes are documented here. For full details see
[Documentation/Changelog/Index.rst](Documentation/Changelog/Index.rst).

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

## [0.3.0] — 2026-04-10

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
