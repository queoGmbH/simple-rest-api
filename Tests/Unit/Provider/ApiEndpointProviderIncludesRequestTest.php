<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Tests\Unit\Provider\Fixture\ControllerWithRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpointProvider::class)]
final class ApiEndpointProviderIncludesRequestTest extends UnitTestCase
{
    #[Test]
    public function providerIncludesServerRequestInterfaceInParameters(): void
    {
        $provider = new ApiEndpointProvider();
        $provider->addEndpoint(
            ControllerWithRequest::class,
            'methodWithRequest',
            'GET',
            '/test/{userId}'
        );

        $endpoints = $provider->getAllEndpoints();
        $endpoint = $endpoints[0];

        // Should have 2 parameters: userId (int) and request (ServerRequestInterface)
        $this->assertSame(2, $endpoint->parameters->count());

        $params = $endpoint->parameters->toArray();
        $this->assertSame('userId', $params[0]->name);
        $this->assertSame('int', $params[0]->type);

        $this->assertSame('request', $params[1]->name);
        $this->assertSame(ServerRequestInterface::class, $params[1]->type);
    }

    #[Test]
    public function providerIncludesRequestAtBeginning(): void
    {
        $provider = new ApiEndpointProvider();
        $provider->addEndpoint(
            ControllerWithRequest::class,
            'methodWithRequestFirst',
            'GET',
            '/test/{userId}'
        );

        $endpoints = $provider->getAllEndpoints();
        $endpoint = $endpoints[0];

        $params = $endpoint->parameters->toArray();
        $this->assertSame('request', $params[0]->name);
        $this->assertSame(ServerRequestInterface::class, $params[0]->type);

        $this->assertSame('userId', $params[1]->name);
        $this->assertSame('int', $params[1]->type);
    }
}
