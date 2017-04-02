<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Class ContentTypeFactory
 */
abstract class ContentTypeFactory
{
    /**
     * @param string $type
     * @return ContentTypeInterface
     */
    public static function getContentType($type)
    {
        return new FallbackContentType($type);
    }

    /**
     * @param array $record
     * @return ContentDataInterface
     */
    public static function getContentData(array $record)
    {
        return StandardContentData::createFromRecord($record);
    }

    /**
     * @param ContentTypeInterface $contentType
     * @return ContentTypeRendererInterface
     */
    public static function getRendererForType(ContentTypeInterface $contentType)
    {
        return new FallbackContentTypeRenderer();
    }
}
