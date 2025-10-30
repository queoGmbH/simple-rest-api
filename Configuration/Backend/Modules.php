<?php

declare(strict_types=1);

use Queo\SimpleRestApi\Controller\Backend\EndpointListController;

return [
    'site_simplerestapi' => [
        'parent' => 'site',
        'position' => ['after' => 'site_redirects'],
        'access' => 'admin',
        'workspaces' => 'live',
        'path' => '/module/site/simple-rest-api',
        'labels' => 'LLL:EXT:simple_rest_api/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'SimpleRestApi',
        'iconIdentifier' => 'simple-rest-api-module',
        'controllerActions' => [
            EndpointListController::class => [
                'list',
            ],
        ],
    ],
];
