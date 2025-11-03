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
    public function collectionCanBeCreatedWithParameters(): void
    {
        $param1 = new ApiEndpointParameter('param1', 'int', 'First parameter');
        $param2 = new ApiEndpointParameter('param2', 'string', 'Second parameter');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $this->assertSame(2, $collection->count());
    }

    #[Test]
    public function collectionCanBeCreatedEmpty(): void
    {
        $collection = new ApiEndpointParameterCollection();

        $this->assertSame(0, $collection->count());
        $this->assertTrue($collection->isEmpty());
    }

    #[Test]
    public function collectionCanGetParameterByName(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $foundParam = $collection->getByName('postId');

        $this->assertSame($param2, $foundParam);
    }

    #[Test]
    public function collectionReturnsNullForNonExistentParameterName(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $foundParam = $collection->getByName('nonExistent');

        $this->assertNull($foundParam);
    }

    #[Test]
    public function collectionCanGetParameterByIndex(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $this->assertSame($param1, $collection->getByIndex(0));
        $this->assertSame($param2, $collection->getByIndex(1));
    }

    #[Test]
    public function collectionReturnsNullForInvalidIndex(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $this->assertNull($collection->getByIndex(999));
    }

    #[Test]
    public function collectionCanCheckIfParameterExists(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $this->assertTrue($collection->hasParameter('userId'));
        $this->assertFalse($collection->hasParameter('nonExistent'));
    }

    #[Test]
    public function collectionCanBeConvertedToArray(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $array = $collection->toArray();

        $this->assertSame([$param1, $param2], $array);
    }

    #[Test]
    public function collectionIsIterable(): void
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
    public function collectionIsCountable(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');
        $param2 = new ApiEndpointParameter('postId', 'int', 'Post ID');

        $collection = new ApiEndpointParameterCollection($param1, $param2);

        $this->assertSame(2, count($collection));
    }

    #[Test]
    public function collectionIsNotEmptyWhenItHasParameters(): void
    {
        $param1 = new ApiEndpointParameter('userId', 'int', 'User ID');

        $collection = new ApiEndpointParameterCollection($param1);

        $this->assertFalse($collection->isEmpty());
    }
}
