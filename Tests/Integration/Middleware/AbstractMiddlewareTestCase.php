<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware;

use RuntimeException;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

// Base test case that initialises Environment for TYPO3 14 compatibility.
abstract class AbstractMiddlewareTestCase extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    // $backupEnvironment is intentionally NOT set to true: UnitTestCase::setUp()
    // would call backupEnvironment() before our setUp() runs, reading
    // Environment::getContext() — an uninitialised typed static in TYPO3 14
    // that throws TypeError.
    //
    // Environment is re-initialised in each setUp() call instead.

    protected function setUp(): void
    {
        parent::setUp();

        // TYPO3 14 made Environment static getters non-nullable. The testing
        // bootstrap leaves them null; initialise with computed paths here.
        // Do NOT read from Environment getters — they may be null at this point.
        $projectPath = realpath(dirname(__DIR__, 3));
        if ($projectPath === false) {
            throw new RuntimeException('Could not resolve project path for Environment::initialize()', 5101367324);
        }

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
