<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\EventListener;

use Queo\SimpleRestApi\Event\ModifyApiResponseEvent;

/**
 * Example event listener for modifying API responses.
 *
 * This class demonstrates how to listen to the ModifyApiResponseEvent
 * and add custom headers to API responses.
 *
 * To use this listener, register it in your Configuration/Services.yaml:
 *
 * services:
 *   Queo\SimpleRestApi\EventListener\ApiResponseModifierExample:
 *     tags:
 *       - name: event.listener
 *         identifier: 'simple-rest-api/response-modifier-example'
 *         event: Queo\SimpleRestApi\Event\ModifyApiResponseEvent
 *         method: 'addCustomHeaders'
 *
 * Common use cases:
 * - Adding CORS headers
 * - Adding API versioning headers
 * - Adding request tracking headers
 * - Adding caching headers
 * - Modifying response content
 */
final readonly class ApiResponseModifierExample
{
    /**
     * Add custom headers to all API responses.
     *
     * This example adds:
     * - CORS headers to allow cross-origin requests
     * - X-API-Version header
     * - X-Request-ID header for request tracking
     */
    public function addCustomHeaders(ModifyApiResponseEvent $event): void
    {
        $response = $event->getResponse();

        // Add CORS headers
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        // Add API version header
        $response = $response->withHeader('X-API-Version', '1.0');

        // Add request ID for tracking (could be generated based on actual request)
        $response = $response->withHeader('X-Request-ID', uniqid('req_', true));

        // Update the response in the event
        $event->setResponse($response);
    }

    /**
     * Example: Add caching headers based on HTTP method.
     *
     * This demonstrates conditional header addition based on the endpoint.
     */
    public function addCachingHeaders(ModifyApiResponseEvent $event): void
    {
        $endpoint = $event->getEndpoint();
        $response = $event->getResponse();

        // Only cache GET requests
        if ($endpoint->httpMethod === 'GET') {
            $response = $response
                ->withHeader('Cache-Control', 'public, max-age=300')
                ->withHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 300));
        } else {
            // Don't cache POST, PUT, PATCH, DELETE
            $response = $response
                ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->withHeader('Pragma', 'no-cache')
                ->withHeader('Expires', '0');
        }

        $event->setResponse($response);
    }

    /**
     * Example: Log response details.
     *
     * This demonstrates accessing request and endpoint information.
     */
    public function logResponse(ModifyApiResponseEvent $event): void
    {
        $endpoint = $event->getEndpoint();
        $apiRequest = $event->getApiRequest();
        $response = $event->getResponse();

        // In a real implementation, you would use a proper logger
        error_log(sprintf(
            'API Response: %s %s -> HTTP %d',
            $endpoint->httpMethod,
            $endpoint->path,
            $response->getStatusCode()
        ));
    }
}
