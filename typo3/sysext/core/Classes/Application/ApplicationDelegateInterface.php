<?php
namespace TYPO3\CMS\Core\Application;

use TYPO3\CMS\Core\Content\ContentTypeInterface;
use TYPO3\CMS\Core\Content\ContentDataInterface;
use TYPO3\CMS\Core\Content\RenderedObjectInterface;
use TYPO3\CMS\Core\Page\PageRendererInterface;

/**
 * Interface ApplicationDelegateInterface
 */
interface ApplicationDelegateInterface
{

    /**
     * @param ContentTypeInterface $contentType
     * @param ContentDataInterface $contentData
     * @return RenderedObjectInterface
     */
    public function renderContentType(ContentTypeInterface $contentType, ContentDataInterface $contentData);

    /**
     * @return PageRendererInterface
     */
    public function getPageRenderer();

}
