<?php

use Queo\SimpleRestApi\Routing\Enhancer\SimpleRestApiEnhancer;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Cache-Konfiguration für die API-Endpunkte
if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['somple_rest_api'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['somple_rest_api'] = [
        'frontend' => VariableFrontend::class,
        'backend' => FileBackend::class,
        'options' => [
            'defaultLifetime' => 3600 * 24 // 1 Tag
        ],
        'groups' => ['system']
    ];
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']['SimpleRestApiEnhancer'] = SimpleRestApiEnhancer::class;

