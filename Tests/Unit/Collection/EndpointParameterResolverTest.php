<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Collection\EndpointParameterResolver;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(EndpointParameterResolver::class)]
final class EndpointParameterResolverTest extends UnitTestCase
{
    #[Test]
    public function resolverGeneratesParameterArrayForMethodWithRequest(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1', 'int'),
                new ApiEndpointParameter('param2', 'string'),
                new ApiEndpointParameter('request', ServerRequestInterface::class)
            ),
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(3, $result);
        $this->assertSame(123, $result[0]);
        $this->assertSame('value', $result[1]);
        $this->assertSame($currentRequest, $result[2]);
    }

    #[Test]
    public function resolverGeneratesParameterArrayForMethodWithoutRequest(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1', 'int'),
                new ApiEndpointParameter('param2', 'string')
            ),
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(2, $result);
        $this->assertSame(123, $result[0]);
        $this->assertSame('value', $result[1]);
    }

    #[Test]
    public function resolverHandlesRequestAtBeginning(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('request', ServerRequestInterface::class),
                new ApiEndpointParameter('userId', 'int')
            ),
            [
                '42'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(2, $result);
        $this->assertSame($currentRequest, $result[0]);
        $this->assertSame(42, $result[1]);
    }

    #[Test]
    public function resolverHandlesRequestInMiddle(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('userId', 'int'),
                new ApiEndpointParameter('request', ServerRequestInterface::class),
                new ApiEndpointParameter('postId', 'int')
            ),
            [
                '42',
                '99'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(3, $result);
        $this->assertSame(42, $result[0]);
        $this->assertSame($currentRequest, $result[1]);
        $this->assertSame(99, $result[2]);
    }

    #[Test]
    public function resolverCastsTypesCorrectly(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('intParam', 'int'),
                new ApiEndpointParameter('stringParam', 'string'),
                new ApiEndpointParameter('floatParam', 'float'),
                new ApiEndpointParameter('boolParam', 'bool')
            ),
            [
                '123',
                'hello',
                '3.14',
                '1'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertSame(123, $result[0]);
        $this->assertIsInt($result[0]);
        $this->assertSame('hello', $result[1]);
        $this->assertIsString($result[1]);
        $this->assertSame(3.14, $result[2]);
        $this->assertIsFloat($result[2]);
        $this->assertSame(true, $result[3]);
        $this->assertIsBool($result[3]);
    }

    #[Test]
    public function resolverReturnsParameterArray(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1', 'int')
            ),
            [
                '123',
                'extra'
            ],
            $currentRequest
        );

        $paramArray = $resolver->getParameterArray();

        $this->assertSame(['123', 'extra'], $paramArray);
    }
}
