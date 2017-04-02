<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Interface ContentTypeRendererInterface
 */
interface ContentTypeRendererInterface
{
    /**
     * @param ContentTypeInterface $contentType
     * @param ContentDataInterface $contentData
     * @return RenderedObjectInterface
     */
    public function render(ContentTypeInterface $contentType, ContentDataInterface $contentData);
}
