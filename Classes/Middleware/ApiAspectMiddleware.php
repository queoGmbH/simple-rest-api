<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Queo\SimpleRestApi\Context\SimpleRestApiAspect;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApiAspectMiddleware implements MiddlewareInterface
{
    public function __construct(private Context $context)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var SimpleRestApiAspect $simpleRestApiAspect */
        $simpleRestApiAspect = GeneralUtility::makeInstance(SimpleRestApiAspect::class, $request);
        $this->context->setAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER, $simpleRestApiAspect);

        return $handler->handle($request);
    }
}
