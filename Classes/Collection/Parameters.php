<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Collection;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Http\ApiRequestInterface;
use Queo\SimpleRestApi\Value\ApiEndpoint;
use Queo\SimpleRestApi\Value\Parameter;
use Queo\SimpleRestApi\Value\ServerRequestInterfaceInjectionException;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

final class Parameters
{
    /**
     * @var Parameter[]
     */
    private array $parameters = [];

    public static function buildFromRequestAndEndpoint(ApiRequestInterface $apiRequest, ApiEndpoint $apiEndpoint, ServerRequestInterface $request): Parameters
    {
        $self = new Parameters();

        $reflectionMethod = new ReflectionMethod($apiEndpoint->className, $apiEndpoint->method);
        $reflectionParams = $reflectionMethod->getParameters();

        $pathParameters = trim(str_replace($apiEndpoint->getPathWithoutParameters(), '', $apiRequest->getEndpointPath()), '/');
        $pathParametersArray = explode('/', $pathParameters);

        foreach ($reflectionParams as $key => $reflectionParam) {

            try {
                $param = Parameter::createFromReflectionParameter($reflectionParam, $pathParametersArray[$key]);
            } catch (ServerRequestInterfaceInjectionException) {
                $param = Parameter::createFromReflectionParameter($reflectionParam, $request);
            }

            if ($param->getName() !== $apiEndpoint->parameterList[$key]) {
                throw new RuntimeException('Parameter name ' . $param->getName() . ' does not match endpoint param ' . $apiEndpoint->parameterList[$key], 7288828913);
            }

            $self->parameters[$param->getName()] = $param;
        }

        return $self;
    }

    public function getMethodParameterArray(): array
    {
        return $this->parameters;
    }

    public function withNewParameterValue(Parameter $parameterToReplace): Parameters
    {
        if (!isset($this->parameters[$parameterToReplace->getName()])) {
            throw new RuntimeException('Parameter ' . $parameterToReplace->getName() . ' does not exist!', 6269803019);
        }

        $self = clone $this;
        $self->parameters[$parameterToReplace->getName()] = $parameterToReplace;
        return $self;
    }
}
