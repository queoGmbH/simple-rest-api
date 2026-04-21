<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

abstract class AbstractMiddlewareTestCase extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected function setUp(): void
    {
        parent::setUp();

        // TYPO3 14 tightened Environment static getters to non-nullable return
        // types. The testing bootstrap may leave them as null, causing TypeErrors
        // when ServerRequestFactory::fromGlobals() is called via NormalizedParams.
        // Initialise with computed paths — do NOT read from Environment here
        // since it may itself be uninitialised at this point.
        $projectPath = (string)realpath(dirname(__DIR__, 3));
        Environment::initialize(
            new ApplicationContext('Testing'),
            PHP_SAPI === 'cli',
            true,
            $projectPath,
            $projectPath . '/.Build/Web',
            $projectPath . '/.Build/var',
            $projectPath . '/config',
            $projectPath . '/.Build/Web/index.php',
            PHP_OS_FAMILY === 'Windows' ? 'WINDOWS' : 'UNIX'
        );
    }
}
