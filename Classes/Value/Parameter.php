<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Value;

use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use RuntimeException;

final class Parameter
{
    public static function createFromReflectionParameter(ReflectionParameter $parameter, int|float|string|bool|object $value)
    {
        if ($parameter->getType()->getName() === ServerRequestInterface::class && !$value instanceof ServerRequestInterface) {
            throw new ServerRequestInterfaceInjectionException('ServerRequestInterface needs to be injected!');
        }

        $castedValue = match($parameter->getType()->getName()) {
            'int' => (int)$value,
            'string' => (string)$value,
            'float' => (float)$value,
            'bool' => $value !== 'false' && (bool)$value,
            default => $value,
        };

        if (!$parameter->getType()->getName() === gettype($value)) {
            throw new RuntimeException('Invalid parameter type.');
        }

        $name = $parameter->getName();

        return new self($name, $castedValue);
    }

    public function __construct(private string $name, private int|float|string|bool|object $value)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
