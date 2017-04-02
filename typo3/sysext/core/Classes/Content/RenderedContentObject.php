<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Class RenderedContentObject
 */
class RenderedContentObject implements RenderedObjectInterface
{
    /**
     * @var string
     */
    protected $header;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var ContentDataInterface
     */
    protected $contentData;

    /**
     * RenderedContentObject constructor.
     * @param string $header
     * @param string $body
     * @param ContentDataInterface $contentData
     */
    public function __construct($header, $body, ContentDataInterface $contentData)
    {
        $this->header = $header;
        $this->body = $body;
        $this->contentData = $contentData;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return sprintf(
            '<div id="%s"><h4>%s</h4>%s',
            $this->contentData->getElementId(),
            $this->header,
            $this->body
        );
    }

}
