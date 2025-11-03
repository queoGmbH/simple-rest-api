<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Tests\Unit\Collection\Fixture\DummyController;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Parameters::class)]
final class ParametersTest extends UnitTestCase
{
    #[Test]
    public function parameters_generate_parameter_array_for_api_method_with_request_object(): void //phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $parameters = new Parameters(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1'),
                new ApiEndpointParameter('param2')
            ),
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $className = DummyController::class;
        $methodName = 'dummyMethodWithRequest';

        $this->assertSame(
            [
            123,
            'value',
            $currentRequest
            ],
            $parameters->buildMethodParameters($className, $methodName)
        );
    }

    #[Test]
    public function parameters_generate_parameter_array_for_api_method_without_request_object(): void //phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $parameters = new Parameters(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1'),
                new ApiEndpointParameter('param2')
            ),
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $className = DummyController::class;
        $methodName = 'dummyMethodWithoutRequest';

        $this->assertSame(
            [
            123,
            'value'
            ],
            $parameters->buildMethodParameters($className, $methodName)
        );
    }
}
