<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Collection;

use Psr\Http\Message\ServerRequestInterface;

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

            // Path parameters are cast to their declared type
            $value = $this->parameterValues[$valueIndex++];
            $methodParameters[] = match ($param->type) {
                'int' => (int)$value,
                'string' => (string)$value,
                'float' => (float)$value,
                'bool' => (bool)$value,
                default => $value,
            };
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
}
