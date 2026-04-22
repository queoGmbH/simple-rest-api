<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Crypto\PasswordHashing\Argon2iPasswordHash;

// Minimal TYPO3 settings for CI E2E test runs.
// DB credentials are read from environment variables set in .gitlab-ci.yml.
return [
    'BE' => [
        'installToolPassword' => '$argon2i$v=19$m=65536,t=16,p=1$ZDVOTGx5VTVOeXVXcVo2OA$p4YRhZmfKY4OKuGAKQjCNOYoo8p+jgj972iz6mIBDas',
        'passwordHashing' => [
            'className' => Argon2iPasswordHash::class,
            'options' => [],
        ],
    ],
    'DB' => [
        'Connections' => [
            'Default' => [
                'charset' => 'utf8',
                'driver' => getenv('TYPO3_DB_DRIVER') ?: 'mysqli',
                'host' => getenv('TYPO3_DB_HOST') ?: '127.0.0.1',
                'port' => (int)(getenv('TYPO3_DB_PORT') ?: 3306),
                'dbname' => getenv('TYPO3_DB_DBNAME') ?: 'typo3_test',
                'user' => getenv('TYPO3_DB_USERNAME') ?: 'root',
                'password' => getenv('TYPO3_DB_PASSWORD') ?: '',
            ],
        ],
    ],
    'EXTENSIONS' => [
        'simple_rest_api' => [
            'basePath' => '/api/',
            'debugMode' => true,
        ],
    ],
    'FE' => [
        'cacheHash' => [
            'enforceValidation' => true,
        ],
        'debug' => false,
        'disableNoCacheParameter' => true,
        'passwordHashing' => [
            'className' => Argon2iPasswordHash::class,
            'options' => [],
        ],
    ],
    'SYS' => [
        'UTF8filesystem' => true,
        'caching' => [
            'cacheConfigurations' => [
                'hash' => [
                    'backend' => Typo3DatabaseBackend::class,
                ],
                'pages' => [
                    'backend' => Typo3DatabaseBackend::class,
                    'options' => [
                        'compression' => true,
                    ],
                ],
                'rootline' => [
                    'backend' => Typo3DatabaseBackend::class,
                    'options' => [
                        'compression' => true,
                    ],
                ],
            ],
        ],
        'devIPmask' => '',
        'displayErrors' => 0,
        'encryptionKey' => 'e2e-ci-fixed-encryption-key-not-secret-572e12bd18d415ff222d08578748ab2958e30736',
        'exceptionalErrors' => 4096,
        'features' => [
            'extbase.consistentDateTimeHandling' => true,
            'frontend.cache.autoTagging' => true,
            'security.system.enforceAllowedFileExtensions' => true,
        ],
        'sitename' => 'simple_rest_api CI',
        'systemMaintainers' => [1],
    ],
];
