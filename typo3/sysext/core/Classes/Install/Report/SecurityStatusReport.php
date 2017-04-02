<?php
namespace TYPO3\CMS\Install\Report;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Service\EnableFileService;
use TYPO3\CMS\Reports\Status;

/**
 * Provides an status report of the security of the install tool
 */
class SecurityStatusReport implements \TYPO3\CMS\Reports\StatusProviderInterface
{
    /**
     * Compiles a collection of system status checks as a status report.
     *
     * @return Status[]
     */
    public function getStatus()
    {
        $this->executeAdminCommand();
        $statuses = [
            'installToolProtection' => $this->getInstallToolProtectionStatus()
        ];
        return $statuses;
    }

    /**
     * Checks for the existence of the ENABLE_INSTALL_TOOL file.
     *
     * @return Status An object representing whether ENABLE_INSTALL_TOOL exists
     */
    protected function getInstallToolProtectionStatus()
    {
        $enableInstallToolFile = PATH_site . EnableFileService::INSTALL_TOOL_ENABLE_FILE_PATH;
        $value = $GLOBALS['LANG']->getLL('status_disabled');
        $message = '';
        $severity = Status::OK;
        if (EnableFileService::installToolEnableFileExists()) {
            if (EnableFileService::isInstallToolEnableFilePermanent()) {
                $severity = Status::WARNING;
                $disableInstallToolUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL') . '&adminCmd=remove_ENABLE_INSTALL_TOOL';
                $value = $GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/Report/locallang.xlf:status_enabledPermanently');
                $message = sprintf($GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:warning.install_enabled'),
                    '<code style="white-space: nowrap;">' . $enableInstallToolFile . '</code>');
                $message .= ' <a href="' . htmlspecialchars($disableInstallToolUrl) . '">' .
                    $GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:warning.install_enabled_cmd') . '</a>';
            } else {
                if (EnableFileService::installToolEnableFileLifetimeExpired()) {
                    EnableFileService::removeInstallToolEnableFile();
                } else {
                    $severity = Status::NOTICE;
                    $disableInstallToolUrl = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL') . '&adminCmd=remove_ENABLE_INSTALL_TOOL';
                    $value = $GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/Report/locallang.xlf:status_enabledTemporarily');
                    $message = sprintf($GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/Report/locallang.xlf:status_installEnabledTemporarily'),
                        '<code style="white-space: nowrap;">' . $enableInstallToolFile . '</code>', floor((@filemtime($enableInstallToolFile) + EnableFileService::INSTALL_TOOL_ENABLE_FILE_LIFETIME - time()) / 60));
                    $message .= ' <a href="' . htmlspecialchars($disableInstallToolUrl) . '">' .
                        $GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:warning.install_enabled_cmd') . '</a>';
                }
            }
        }
        return GeneralUtility::makeInstance(Status::class,
            $GLOBALS['LANG']->sL('LLL:EXT:core/Resources/Private/Language/Report/locallang.xlf:status_installTool'), $value, $message, $severity);
    }

    /**
     * Executes commands like removing the Install Tool enable file.
     */
    protected function executeAdminCommand()
    {
        $command = GeneralUtility::_GET('adminCmd');
        switch ($command) {
            case 'remove_ENABLE_INSTALL_TOOL':
                EnableFileService::removeInstallToolEnableFile();
                break;
            default:
                // Do nothing
        }
    }
}
