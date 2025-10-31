<?php

use Queo\SimpleRestApi\Routing\Enhancer\SimpleRestApiEnhancer;
use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

// Register custom icon
$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon(
    'simple-rest-api-module',
    SvgIconProvider::class,
    ['source' => 'EXT:simple_rest_api/Resources/Public/Icons/module-simple-rest-api.svg']
);

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
