<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

abstract class AbstractMiddlewareTestCase extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected function setUp(): void
    {
        parent::setUp();

        // TYPO3 14 tightened Environment::getCurrentScript() to a non-nullable
        // return type. The testing bootstrap may leave $currentScript as null,
        // causing a TypeError when ServerRequestFactory::fromGlobals() is called.
        // Re-initialise with all existing values + a valid currentScript.
        Environment::initialize(
            Environment::getContext(),
            Environment::isCli(),
            Environment::isComposerMode(),
            Environment::getProjectPath(),
            Environment::getPublicPath(),
            Environment::getVarPath(),
            Environment::getConfigPath(),
            Environment::getPublicPath() . '/index.php',
            PHP_OS_FAMILY === 'Windows' ? 'WINDOWS' : 'UNIX'
        );
    }
}
