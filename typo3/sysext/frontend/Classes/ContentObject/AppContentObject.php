<?php
namespace TYPO3\CMS\Frontend\ContentObject;

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

use TYPO3\CMS\Core\Application\ApplicationDelegateFactory;
use TYPO3\CMS\Core\Content\ContentTypeFactory;

/**
 * Contains APP class object.
 */
class AppContentObject extends AbstractContentObject
{
    /**
     * Rendering the cObject, USER
     *
     * @param array $conf Array of TypoScript properties
     * @return string Output
     */
    public function render($conf = [])
    {
        return call_user_func([$this, $conf['method'] ?? 'renderApplicationIdentity']);
    }

    /**
     * @return string
     */
    protected function renderApplicationIdentity()
    {
        return static::class;
    }

    /**
     * @return string
     */
    protected function renderContentType()
    {
        $type = ContentTypeFactory::getContentType($this->cObj->data['CType']);
        $data = ContentTypeFactory::getContentData($this->cObj->data);
        return ApplicationDelegateFactory::getConfiguredApplicationDelegate()->renderContentType($type, $data)->getContent();
    }
}
