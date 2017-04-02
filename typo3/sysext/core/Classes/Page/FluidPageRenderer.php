<?php
namespace TYPO3\CMS\Core\Page;

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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * Class FluidPageRenderer
 */
class FluidPageRenderer extends AbstractPageRenderer implements SingletonInterface, PageRendererInterface
{
    /**
     * @param int $part
     * @return string
     */
    public function render($part = self::PART_COMPLETE)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $controllerContext = $objectManager->get(ControllerContext::class);
        $controllerContext->setRequest(new Request());
        $controllerContext->setResponse(new Response());
        $view = $objectManager->get(TemplateView::class);
        $view->setControllerContext($controllerContext);
        $view->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('frontend');
        $view->getRenderingContext()->setControllerName('Page');
        $view->getRenderingContext()->setControllerAction('Default');
        $view->assignMultiple([
            'xmlPrologue' => $this->xmlPrologAndDocType,
            'headerComments' => $this->inlineComments,
            'headerCode' => $this->headerData,
            'footerCode' => $this->footerData
        ]);
        switch ($part) {
            case self::PART_COMPLETE:
                return $view->render();
            case self::PART_HEADER:
                return $view->renderSection('Header', [], true);
            case self::PART_FOOTER:
                return $view->renderSection('Footer', [], true);
        }
        return 'Unknown part: ' . $part;
    }

    /**
     * @param string $substituteHash
     * @return string
     */
    public function renderPageWithUncachedObjects($substituteHash)
    {
        return 'BOOYA!';
    }
}
