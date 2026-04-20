<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Dev\EventListener;

use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * Adds known headers to every API response so that Playwright E2E tests can
 * assert that ModifyApiResponseEvent is dispatched and that event data is
 * accessible inside listeners.
 *
 * Registered only in Development context via the runtime guard in
 * Configuration/Services.php. Must NOT be loaded in production.
 */
final class E2eTestResponseListener
{
    #[AsEventListener('e2e-test-response-listener')]
    public function __invoke(ModifyApiResponseEvent $event): void
    {
        $response = $event->getResponse()
            ->withHeader('X-E2E-Modified', 'true')
            ->withHeader('X-E2E-Endpoint', $event->getEndpoint()->path);

        $event->setResponse($response);
    }
}
