<?php
namespace TYPO3\CMS\Fluid\Core\ViewHelper;

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

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\Traits\ControllerContextAware;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * The abstract base class for all view helpers.
 *
 * @api
 */
abstract class AbstractViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper implements ViewHelperInterface
{
    use ControllerContextAware;

    /**
     * Call the render() method and handle errors.
     *
     * @return string the rendered ViewHelper
     * @throws Exception
     */
    protected function callRenderMethod()
    {
        try {
            return call_user_func([$this, 'render']);
        } catch (Exception $exception) {
            if (GeneralUtility::getApplicationContext()->isProduction()) {
                $this->getLogger()->error('A Fluid ViewHelper Exception was captured: ' . $exception->getMessage() . ' (' . $exception->getCode() . ')', ['exception' => $exception]);
                return '';
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }
}
