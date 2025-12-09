<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller\Backend;

use Queo\SimpleRestApi\Value\ApiEndpoint;
use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfigurationInterface;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class EndpointListController extends ActionController
{
    public function __construct(
        private readonly ApiEndpointProvider $endpointProvider,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly ExtensionConfigurationInterface $extensionConfiguration
    ) {
    }

    public function listAction(): ResponseInterface
    {
        $endpoints = $this->endpointProvider->getAllEndpoints();

        // Filter out internal endpoints unless explicitly enabled
        if (!$this->extensionConfiguration->showInternalEndpoints()) {
            $endpoints = array_filter(
                $endpoints,
                fn(ApiEndpoint $endpoint): bool => !$endpoint->hasTag('internal')
            );
        }

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setTitle('REST API Endpoints');
        $moduleTemplate->assign('endpoints', $endpoints);

        return $moduleTemplate->renderResponse('Backend/EndpointList/List');
    }
}
