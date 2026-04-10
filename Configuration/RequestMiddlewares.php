<?php

declare(strict_types=1);

use Queo\SimpleRestApi\Middleware\ApiAspectMiddleware;
use Queo\SimpleRestApi\Middleware\ApiResolverMiddleware;
use Queo\SimpleRestApi\Middleware\CacheHashFixer;

return [
    'frontend' => [
        'queo/simple-rest-api/api-resolver-middleware' => [
            'target' => ApiResolverMiddleware::class,
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
        'queo/simple-rest-api/cache-hash-fixer' => [
            'target' => CacheHashFixer::class,
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
        'queo/simple-rest-api/api-aspect-middleware' => [
            'target' => ApiAspectMiddleware::class,
            'before' => [
                'typo3/cms-frontend/page-resolver',
                'queo/simple-rest-api/cache-hash-fixer',
                'queo/simple-rest-api/api-resolver-middleware',
            ],
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];
