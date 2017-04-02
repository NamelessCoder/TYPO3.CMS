<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Class FallbackContentType
 */
class FallbackContentType implements ContentTypeInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }
}
