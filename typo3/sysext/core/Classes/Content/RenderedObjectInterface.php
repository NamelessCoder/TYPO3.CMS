<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Interface RenderedObjectInterface
 */
interface RenderedObjectInterface
{
    /**
     * RenderedContentObject constructor.
     * @param string $header
     * @param string $body
     * @param ContentDataInterface $contentData
     */
    public function __construct($header, $body, ContentDataInterface $contentData);

    /**
     * @return string
     */
    public function getBody();

    /**
     * @return string
     */
    public function getHeader();

    /**
     * @return string
     */
    public function getContent();

}
