# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- ModifyApiResponseEvent to allow response modifications before returning to client
- Color-coded HTTP method badges in backend module endpoint list
- Comprehensive quick start guide in README
- Complete method examples for all HTTP methods in documentation

### Changed
- Restructured PSR-14 events documentation for better clarity
- Replaced 'Adding Request Context' example with 'Loading Extbase Models' example
- Updated README with project badges and improved documentation

### Fixed
- Removed reference to non-existent event-flow.svg image

### Maintenance
- Added date extension to composer-require-checker config
- Excluded CLAUDE.md from package distribution
- Added .phpunit.result.cache and .claude/ to .gitignore
- Removed config folder from repository
- Fixed Rector code style issues

## [0.2.0-rc1] - 2024-XX-XX

### Added
- Initial release candidate with core REST API functionality
- AsApiEndpoint attribute for marking methods as API endpoints
- ApiResolverMiddleware for endpoint resolution
- Backend module for listing registered endpoints
- Route enhancer for /api/* path handling
- PSR-14 events for parameter mapping

[Unreleased]: https://gitlab.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/-/compare/0.2.0-rc1...main
[0.2.0-rc1]: https://gitlab.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/-/tags/0.2.0-rc1
