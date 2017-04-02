<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Class FallbackContentType
 */
class FallbackContentTypeRenderer implements ContentTypeRendererInterface
{
    /**
     * @param ContentTypeInterface $contentType
     * @param ContentDataInterface $contentData
     * @return RenderedContentObject
     */
    public function render(ContentTypeInterface $contentType, ContentDataInterface $contentData)
    {
        return new RenderedContentObject('RENDERED!', var_export($contentType, true), $contentData);
    }
}
