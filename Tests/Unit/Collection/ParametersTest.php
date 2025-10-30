<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Collection;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\Parameters;
use Queo\SimpleRestApi\Tests\Unit\Collection\Fixture\DummyController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \Queo\SimpleRestApi\Collection\Parameters
 */
final class ParametersTest extends UnitTestCase
{
    /**
     * @test
     */
    public function parameters_generate_parameter_array_for_api_method_with_request_object(): void //phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $parameters = new Parameters(
            [
                0 => 'param1',
                1 => 'param2'
            ],
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

    /**
     * @test
     */
    public function parameters_generate_parameter_array_for_api_method_without_request_object(): void //phpcs:ignore
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $parameters = new Parameters(
            [
                0 => 'param1',
                1 => 'param2'
            ],
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
