<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Attribute;

use Attribute;

/**
 * Marks a method as a REST API endpoint.
 *
 * @api
 *
 * SECURITY NOTICE: This extension handles routing and parameter mapping only.
 * Authentication, authorization, rate limiting, and input validation beyond
 * scalar type coercion are the responsibility of the consumer.
 * See the security guidelines for recommended patterns.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class AsApiEndpoint
{
    public const TAG_NAME = 'api.endpoint';

    /**
     * @param array<string> $tags
     */
    public function __construct(
        public string $method,
        public string $path,
        public string $summary = '',
        public string $description = '',
        public array $tags = []
    ) {
    }
}
