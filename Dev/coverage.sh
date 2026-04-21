#!/bin/bash
# Run PHPUnit with pcov coverage using the project test runner image.
# Requires: docker login dockerhub.cloud.queo.org (one-time setup)
#
# Usage: ./Dev/coverage.sh

set -e

IMAGE="dockerhub.cloud.queo.org/pwmuc/packages/typo3/simple-rest-api/test-runner:8.4"

docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  "$IMAGE" \
  sh -c "
    php .Build/bin/phpunit -c phpunit-integration.xml --coverage-text && \
    php .Build/bin/phpunit -c phpunit.xml --coverage-text
  "
