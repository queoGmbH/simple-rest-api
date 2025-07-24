<?php

namespace Queo\SimpleRestApi\Routing\Enhancer;

use Queo\SimpleRestApi\Context\SimpleRestApiAspect;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Routing\Enhancer\AbstractEnhancer;
use TYPO3\CMS\Core\Routing\Enhancer\RoutingEnhancerInterface;
use TYPO3\CMS\Core\Routing\RouteCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 * ```
 * routeEnhancers:
 *   SimpleRestApiEnhancer:
 *     type: SimpleRestApiEnhancer
 * ```
 */
class SimpleRestApiEnhancer extends AbstractEnhancer implements RoutingEnhancerInterface
{
    public const ENHANCER_NAME = 'SimpleRestApiEnhancer';

    /**
     * @param array<string> $configuration
     */
    public function __construct(protected array $configuration)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function enhanceForMatching(RouteCollection $collection): void
    {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var SimpleRestApiAspect $visitorFeedbackAspect */
        $visitorFeedbackAspect = $context->getAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER);

        $basePath = rtrim($visitorFeedbackAspect->getConfiguration()->getApiBasePath(), '/') . '/';
        $variant = clone $collection->get('default');
        $variant->setPath($basePath . '{params?}');
        $variant->setRequirement('params', '.*');

        $collection->add('enhancer_' . $basePath . spl_object_hash($variant), $variant);
    }

    /**
     * {@inheritdoc}
     */
    public function enhanceForGeneration(RouteCollection $collection, array $parameters): void
    {
    }
}
