<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Collection;

use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Parameters
{
    /**
     * @param array<string> $endpointParameters
     * @param array<string> $parameterValues
     */
    public function __construct(
        private array $endpointParameters,
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

            if ($paramName !== $this->endpointParameters[$key]) {
                throw new RuntimeException('Parameter name ' . $paramName . ' does not match endpoint param ' . $this->endpointParameters[$key], 7288828913);
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
}
