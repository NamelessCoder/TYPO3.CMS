<?php
namespace TYPO3\CMS\Core\Application;

use TYPO3\CMS\Core\Content\ContentDataInterface;
use TYPO3\CMS\Core\Content\ContentTypeFactory;
use TYPO3\CMS\Core\Content\ContentTypeInterface;
use TYPO3\CMS\Core\Content\RenderedObjectInterface;
use TYPO3\CMS\Core\Page\FluidPageRenderer;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Page\PageRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class StandardApplicationDelegate
 */
class StandardApplicationDelegate implements ApplicationDelegateInterface
{
    /**
     * @param ContentTypeInterface $contentType
     * @param ContentDataInterface $contentData
     * @return RenderedObjectInterface
     */
    public function renderContentType(ContentTypeInterface $contentType, ContentDataInterface $contentData)
    {
        return ContentTypeFactory::getRendererForType($contentType)->render($contentType, $contentData);
    }

    /**
     * @return PageRendererInterface
     */
    public function getPageRenderer()
    {
        if (TYPO3_MODE === 'FE') {
            return GeneralUtility::makeInstance(FluidPageRenderer::class);
        }
        return GeneralUtility::makeInstance(PageRenderer::class);
    }
}
