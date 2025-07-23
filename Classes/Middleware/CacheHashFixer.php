<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;

class CacheHashFixer implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Site $site */
        $site = $request->getAttribute('site');

        // check if the URL is addressing the endpoint for visitor feedback
        if ($site && !$site instanceof NullSite) {
            $path = $request->getUri()->getPath() ?: '/';
            // @todo: Use base path from ext configuration
            $basePath = $site->getBase()->getPath() . 'api/';

            // if yes: Override the settings in the `LocalConfiguration.php`
            if (str_starts_with($path, $basePath)) {
                $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFoundOnCHashError'] = false;
                $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['enforceValidation'] = false;
            }
        }

        return $handler->handle($request);
    }
}
