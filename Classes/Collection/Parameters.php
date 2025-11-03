<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Collection;

use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

// @todo: find a proper name for this class.
final readonly class Parameters
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
     * @return array<mixed>
     * @throws ReflectionException
     */
    public function buildMethodParameters(string $className, string $methodName): array
    {
        // @todo: Is this necessary? We get a collection with ApiEndpointParameter objects, which were extracted via Reflection
        //        from the Endpoint method - doing this here again makes no sense, we can just walk over ApiEndpointCollection here.
        $reflectionMethod = new ReflectionMethod($className, $methodName);
        $reflectionParams = $reflectionMethod->getParameters();
        $methodParameters = [];

        foreach ($reflectionParams as $key => $reflectionParam) {
            $type = null;
            $reflectionType = $reflectionParam->getType();

            if ($reflectionType instanceof ReflectionNamedType) {
                $type = $reflectionType->getName();
            }

            $paramName = $reflectionParam->getName();

            if ($type === ServerRequestInterface::class) {
                $methodParameters[] = $this->request;
                continue;
            }

            // @todo: Question: Is this check necessary? @see: Line 33 this file.
            $endpointParam = $this->endpointParameters->getByIndex($key);
            if ($endpointParam === null || $paramName !== $endpointParam->name) {
                $endpointParamName = $endpointParam !== null ? $endpointParam->name : 'null';
                throw new RuntimeException('Parameter name ' . $paramName . ' does not match endpoint param ' . $endpointParamName, 7288828913);
            }

            $methodParameters[] = match ($type) {
                'int' => (int)$this->parameterValues[$key],
                'string' => (string)$this->parameterValues[$key],
                'float' => (float)$this->parameterValues[$key],
                'bool' => (bool)$this->parameterValues[$key],
                default => $this->parameterValues[$key],
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
