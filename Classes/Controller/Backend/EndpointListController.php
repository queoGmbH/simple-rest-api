<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller\Backend;

use TYPO3\CMS\Core\Site\Entity\Site;
use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfigurationInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class EndpointListController extends ActionController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly ExtensionConfigurationInterface $extensionConfiguration,
        private readonly PageRenderer $pageRenderer
    ) {
    }

    public function listAction(): ResponseInterface
    {
        // Get site from request to build OpenAPI spec URL
        $site = $this->request->getAttribute('site');
        $baseUrl = $site instanceof Site ? $site->getBase()->__toString() : '';
        $basePath = $this->extensionConfiguration->getApiBasePath();
        $openApiUrl = rtrim($baseUrl, '/') . rtrim($basePath, '/') . '/openapi.json';

        // Include Swagger UI assets via PageRenderer
        $this->pageRenderer->addCssFile('EXT:simple_rest_api/Resources/Public/JavaScript/SwaggerUI/swagger-ui.css');
        $this->pageRenderer->addCssFile('EXT:simple_rest_api/Resources/Public/Css/swagger-backend.css');
        $this->pageRenderer->addJsFile('EXT:simple_rest_api/Resources/Public/JavaScript/SwaggerUI/swagger-ui-bundle.js', 'text/javascript', false, false, '', true);
        $this->pageRenderer->addJsFile('EXT:simple_rest_api/Resources/Public/JavaScript/SwaggerUI/swagger-ui-standalone-preset.js', 'text/javascript', false, false, '', true);
        $this->pageRenderer->addJsFile('EXT:simple_rest_api/Resources/Public/JavaScript/swagger-init.js', 'text/javascript', false, false, '', true);

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setTitle('REST API Endpoints');
        $moduleTemplate->assign('openApiUrl', $openApiUrl);

        return $moduleTemplate->renderResponse('Backend/EndpointList/List');
    }
}
