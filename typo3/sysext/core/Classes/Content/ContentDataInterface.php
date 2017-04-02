<?php
namespace TYPO3\CMS\Core\Content;

/**
 * Interface ContentDataInterface
 */
interface ContentDataInterface
{

    /**
     * @param array $record
     * @return ContentDataInterface
     */
    public static function createFromRecord(array $record);

    /**
     * @return string
     */
    public function getElementId();

}
