<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Http\ApiRequest;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Tests\Unit\Collection\Fixture\DummyController;
use Queo\SimpleRestApi\Tests\Unit\Value\Fixture\DummyObject;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use Queo\SimpleRestApi\Value\ApiEndpointInterface;
use Queo\SimpleRestApi\Value\Parameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Parameters::class)]
final class ParametersTest extends UnitTestCase
{
    #[Test]
    public function parameters_generate_parameter_array_for_api_method_with_request_object(): void //phpcs:ignore
    {
        $className = DummyController::class;
        $methodName = 'dummyMethodWithRequest';

        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiEndpoint = new ApiEndpoint(
            $className,
            $methodName,
            '/some/dummy/path/{param1}/{param2}',
            'GET',
            [
                0 => 'param1',
                1 => 'param2'
            ]
        );

        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/some/dummy/path/123/value');

        $parameters = Parameters::buildFromRequestAndEndpoint($apiRequest, $apiEndpoint, $currentRequest);

        $this->assertEquals(
            [
                'param1' => new Parameter('param1', 123),
                'param2' => new Parameter('param2', 'value'),
                'request' => new Parameter('request', $currentRequest)
            ],
            $parameters->getMethodParameterArray()
        );
    }

    #[Test]
    public function parameters_generate_parameter_array_for_api_method_without_request_object(): void //phpcs:ignore
    {
        $className = DummyController::class;
        $methodName = 'dummyMethodWithoutRequest';

        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiEndpoint = new ApiEndpoint(
            $className,
            $methodName,
            '/some/dummy/path/{param1}/{param2}',
            'GET',
            [
                0 => 'param1',
                1 => 'param2'
            ]
        );

        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/some/dummy/path/123/value');

        $parameters = Parameters::buildFromRequestAndEndpoint($apiRequest, $apiEndpoint, $currentRequest);

        $this->assertEquals(
            [
                'param1' => new Parameter('param1', 123),
                'param2' => new Parameter('param2', 'value')
            ],
            $parameters->getMethodParameterArray()
        );
    }

    #[Test]
    public function parameters_can_be_changed_but_immutable(): void
    {
        $className = DummyController::class;
        $methodName = 'dummyMethodWithoutRequest';

        $currentRequest = $this->createMock(ServerRequestInterface::class);
        $apiRequest = $this->createMock(ApiRequestInterface::class);
        $apiEndpoint = new ApiEndpoint(
            $className,
            $methodName,
            '/some/dummy/path/{param1}/{param2}',
            'GET',
            [
                0 => 'param1',
                1 => 'param2'
            ]
        );

        $apiRequest->expects(self::once())->method('getEndpointPath')->willReturn('/some/dummy/path/123/value');

        $parameters = Parameters::buildFromRequestAndEndpoint($apiRequest, $apiEndpoint, $currentRequest);

        $dummyObject = new DummyObject();
        $replaceParameter = new Parameter('param1', $dummyObject);

        $actualParameters = $parameters->withNewParameterValue($replaceParameter);

        $this->assertNotSame($parameters, $actualParameters);

        $this->assertEquals(
            [
                'param1' => new Parameter('param1', $dummyObject),
                'param2' => new Parameter('param2', 'value')
            ],
            $actualParameters->getMethodParameterArray()
        );
    }
}
