<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpointParameterCollection::class)]
final class ApiEndpointParameterCollectionTest extends UnitTestCase
{
    #[Test]
    public function collection_can_be_created_with_parameters(): void
    {
        $param1 = new ApiEndpointParameter('param1', 'int', 'First parameter');
        $param2 = new ApiEndpointParameter('param2', 'string', 'Second parameter');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $this->assertSame(2, $collection->count());
    }

    #[Test]
    public function collection_can_be_created_empty(): void
    {
        $collection = new ApiEndpointParameterCollection();

        $this->assertSame(0, $collection->count());
        $this->assertTrue($collection->isEmpty());
    }

    #[Test]
    public function collection_can_get_parameter_by_name(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $foundParam = $collection->getByName('postId');

        $this->assertSame($param2, $foundParam);
    }

    #[Test]
    public function collection_returns_null_for_non_existent_parameter_name(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $foundParam = $collection->getByName('nonExistent');

        $this->assertNull($foundParam);
    }

    #[Test]
    public function collection_can_get_parameter_by_index(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $this->assertSame($param1, $collection->getByIndex(0));
        $this->assertSame($param2, $collection->getByIndex(1));
    }

    #[Test]
    public function collection_returns_null_for_invalid_index(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $this->assertNull($collection->getByIndex(999));
    }

    #[Test]
    public function collection_can_check_if_parameter_exists(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $this->assertTrue($collection->hasParameter('userId'));
        $this->assertFalse($collection->hasParameter('nonExistent'));
    }

    #[Test]
    public function collection_can_be_converted_to_array(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $array = $collection->toArray();

        $this->assertSame([$param1, $param2], $array);
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $result = [];
        foreach ($collection as $parameter) {
            $result[] = $parameter;
        }

        $this->assertSame([$param1, $param2], $result);
    }

    #[Test]
    public function collection_is_countable(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $this->assertSame(2, count($collection));
    }

    #[Test]
    public function collection_is_not_empty_when_it_has_parameters(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $this->assertFalse($collection->isEmpty());
    }
}
