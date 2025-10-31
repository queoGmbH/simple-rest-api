<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use Queo\SimpleRestApi\Context\SimpleRestApiAspect;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\JsonResponse;

final readonly class TestController
{
    private ServerRequestInterface $request;

    public function __construct(private Context $context)
    {
        /** @var SimpleRestApiAspect $simpleRestApi */
        $simpleRestApi = $this->context->getAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER);
        $this->request = $simpleRestApi->getRequest();
    }

    #[AsApiEndpoint(
        method: 'GET',
        path: '/v1/my-get-endpoint',
        summary: 'Basic GET endpoint example',
        description: 'Demonstrates a simple GET endpoint that returns success status, current URL, and language information from TYPO3 context'
    )]
    public function myEndpoint(): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId
        ]);
    }

    #[AsApiEndpoint(
        method: 'POST',
        path: '/v1/my-post-endpoint',
        summary: 'POST endpoint example',
        description: 'Shows how to create a POST endpoint. Access request body via $this->request or inject ServerRequestInterface as method parameter'
    )]
    public function someOtherEndpoint(): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId
        ]);
    }

    /**
     * @param int $param1 The first numeric parameter (e.g., user ID or record ID)
     * @param string $param2 The second string parameter (e.g., username or slug)
     */
    #[AsApiEndpoint(
        method: 'GET',
        path: '/v1/my-param-endpoint/{param1}/{param2}',
        summary: 'Endpoint with URL parameters',
        description: 'Demonstrates parameter mapping from URL path to method arguments. Parameters are automatically type-cast (int, string, etc.) and passed to the method'
    )]
    public function someParamEndpoint(int $param1, string $param2): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId,
            'parameters' => [$param1, $param2]
        ]);
    }

    /**
     * @param int $resourceId The ID of the resource to update
     */
    #[AsApiEndpoint(
        method: 'PUT',
        path: '/v1/resources/{resourceId}',
        summary: 'PUT endpoint for full resource update',
        description: 'Demonstrates a PUT endpoint for replacing an entire resource. Typically used for full updates where all fields are provided'
    )]
    public function updateResource(int $resourceId): JsonResponse
    {
        // In a real implementation, you would:
        // 1. Get request body: $body = json_decode($this->request->getBody()->getContents(), true);
        // 2. Validate the data
        // 3. Update the entire resource with $resourceId
        // 4. Return the updated resource

        return new JsonResponse([
            'success' => true,
            'message' => 'Resource updated (full replacement)',
            'resourceId' => $resourceId,
            'method' => 'PUT'
        ]);
    }

    /**
     * @param int $resourceId The ID of the resource to partially update
     */
    #[AsApiEndpoint(
        method: 'PATCH',
        path: '/v1/resources/{resourceId}',
        summary: 'PATCH endpoint for partial resource update',
        description: 'Demonstrates a PATCH endpoint for partially updating a resource. Only provided fields are updated, leaving others unchanged'
    )]
    public function patchResource(int $resourceId): JsonResponse
    {
        // In a real implementation, you would:
        // 1. Get request body: $body = json_decode($this->request->getBody()->getContents(), true);
        // 2. Validate the provided fields
        // 3. Update only the provided fields of the resource with $resourceId
        // 4. Return the updated resource

        return new JsonResponse([
            'success' => true,
            'message' => 'Resource partially updated',
            'resourceId' => $resourceId,
            'method' => 'PATCH'
        ]);
    }

    /**
     * @param int $resourceId The ID of the resource to delete
     */
    #[AsApiEndpoint(
        method: 'DELETE',
        path: '/v1/resources/{resourceId}',
        summary: 'DELETE endpoint for resource removal',
        description: 'Demonstrates a DELETE endpoint for removing a resource. Returns success confirmation after deletion'
    )]
    public function deleteResource(int $resourceId): JsonResponse
    {
        // In a real implementation, you would:
        // 1. Validate that the resource exists
        // 2. Check permissions
        // 3. Delete the resource with $resourceId
        // 4. Return success confirmation

        return new JsonResponse([
            'success' => true,
            'message' => 'Resource deleted',
            'resourceId' => $resourceId,
            'method' => 'DELETE'
        ], 200); // Some APIs use 204 No Content for successful deletes
    }
}
