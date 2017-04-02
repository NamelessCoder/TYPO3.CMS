<?php
namespace TYPO3\CMS\Core\Application;

/**
 * Class ApplicationDelegateFactory
 */
abstract class ApplicationDelegateFactory
{
    /**
     * @return ApplicationDelegateInterface
     */
    public static function getConfiguredApplicationDelegate()
    {
        return new $GLOBALS['TYPO3_CONF_VARS']['APP']['delegate']();
    }
}
