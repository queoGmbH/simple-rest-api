<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Collection;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Exception\InvalidParameterException;

/**
 * Resolves endpoint parameters to method arguments.
 *
 * This class takes:
 * - Metadata about endpoint parameters (ApiEndpointParameterCollection)
 * - Raw parameter values from the URL (array of strings)
 * - The HTTP request (ServerRequestInterface)
 *
 * And builds a properly typed array of arguments to invoke the API endpoint method.
 */
final readonly class EndpointParameterResolver
{
    /**
     * @param array<string> $parameterValues
     */
    public function __construct(
        private ApiEndpointParameterCollection $endpointParameters,
        private array $parameterValues,
        private ServerRequestInterface $request
    ) {
    }

    /**
     * Build method parameters ready for invocation.
     *
     * @return array<mixed>
     * @throws InvalidParameterException when a URL parameter value cannot be coerced to its declared type
     */
    public function buildMethodParameters(): array
    {
        $methodParameters = [];
        $valueIndex = 0;

        foreach ($this->endpointParameters as $param) {
            // ServerRequestInterface is injected from the current request
            if ($param->type === ServerRequestInterface::class) {
                $methodParameters[] = $this->request;
                continue;
            }

            $value = $this->parameterValues[$valueIndex++];
            $methodParameters[] = $this->coerce($param->name, $param->type, $value);
        }

        return $methodParameters;
    }

    /**
     * Get the raw parameter values array.
     *
     * @return array<string>
     */
    public function getParameterArray(): array
    {
        return $this->parameterValues;
    }

    /**
     * Coerce a raw string value to the declared parameter type.
     *
     * @throws InvalidParameterException
     */
    private function coerce(string $name, string $type, string $value): mixed
    {
        return match ($type) {
            'int' => $this->coerceInt($name, $value),
            'float' => $this->coerceFloat($name, $value),
            'bool' => $this->coerceBool($name, $value),
            'string' => $value,
            default => $value,
        };
    }

    /**
     * @throws InvalidParameterException
     */
    private function coerceInt(string $name, string $value): int
    {
        $result = filter_var($value, FILTER_VALIDATE_INT);

        if ($result === false) {
            throw new InvalidParameterException(
                sprintf("Parameter '%s' must be an integer, got: '%s'", $name, $value),
                6762494978
            );
        }

        return $result;
    }

    /**
     * @throws InvalidParameterException
     */
    private function coerceFloat(string $name, string $value): float
    {
        $result = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($result === false) {
            throw new InvalidParameterException(
                sprintf("Parameter '%s' must be a float, got: '%s'", $name, $value),
                8081526214
            );
        }

        return $result;
    }

    /**
     * @throws InvalidParameterException
     */
    private function coerceBool(string $name, string $value): bool
    {
        // PHP's FILTER_VALIDATE_BOOLEAN treats '' as false — reject it explicitly
        // to avoid silent coercion of empty URL segments.
        if ($value === '') {
            throw new InvalidParameterException(
                sprintf("Parameter '%s' must be a boolean (1/0, true/false, yes/no, on/off), got: '%s'", $name, $value),
                7047125565
            );
        }

        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($result === null) {
            throw new InvalidParameterException(
                sprintf("Parameter '%s' must be a boolean (1/0, true/false, yes/no, on/off), got: '%s'", $name, $value),
                6224645969
            );
        }

        return $result;
    }
}
