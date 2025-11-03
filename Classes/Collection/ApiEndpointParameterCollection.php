<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Collection;

use Countable;
use IteratorAggregate;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use Traversable;

/**
 * A type-safe collection for API endpoint parameters.
 *
 * @implements IteratorAggregate<int, ApiEndpointParameter>
 */
final readonly class ApiEndpointParameterCollection implements IteratorAggregate, Countable
{
    /** @var array<int, ApiEndpointParameter> */
    private array $parameters;

    public function __construct(ApiEndpointParameter ...$parameters)
    {
        $this->parameters = array_values($parameters);
    }

    /**
     * Get a parameter by its name.
     */
    public function getByName(string $name): ?ApiEndpointParameter
    {
        foreach ($this->parameters as $parameter) {
            if ($parameter->name === $name) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * Get a parameter by its index.
     */
    public function getByIndex(int $index): ?ApiEndpointParameter
    {
        return $this->parameters[$index] ?? null;
    }

    /**
     * Check if a parameter with the given name exists.
     */
    public function hasParameter(string $name): bool
    {
        return $this->getByName($name) !== null;
    }

    /**
     * Get the number of parameters in this collection.
     */
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * Check if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Get all parameters as an array.
     *
     * @return array<int, ApiEndpointParameter>
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * Get an iterator for the parameters.
     *
     * @return Traversable<int, ApiEndpointParameter>
     */
    public function getIterator(): Traversable
    {
        yield from $this->parameters;
    }
}
