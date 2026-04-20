#!/bin/bash
# Run PHPUnit with pcov coverage using the project test runner image.
# Requires: docker login registry.gitlab.cloud.queo.org (one-time setup)
#
# Usage: ./Dev/coverage.sh [phpunit-args]
# Example: ./Dev/coverage.sh --filter MyTest

set -e

IMAGE="${CI_REGISTRY_IMAGE:-registry.gitlab.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api}/test-runner:8.4"

docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  "$IMAGE" \
  php .Build/bin/phpunit -c phpunit.xml --coverage-text "$@"
