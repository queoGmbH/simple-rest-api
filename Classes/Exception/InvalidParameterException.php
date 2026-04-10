<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Exception;

use RuntimeException;

/**
 * Thrown when a URL parameter cannot be coerced to the declared type.
 *
 * Caught by ApiResolverMiddleware and converted to a 400 Bad Request response.
 */
final class InvalidParameterException extends RuntimeException
{
}
