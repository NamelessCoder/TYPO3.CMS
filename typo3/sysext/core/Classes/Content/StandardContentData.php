<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Class StandardContentData
 */
class StandardContentData implements ContentDataInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $record
     * @return ContentDataInterface
     */
    public static function createFromRecord(array $record)
    {
        return new static($record);
    }

    /**
     * @return int
     */
    public function getElementId()
    {
        return $this->data['uid'] ?? 0;
    }
}
