<?php
namespace TYPO3\CMS\Fluid\Core\ViewHelper\Traits;

use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class ControllerContextAware
 */
trait ControllerContextAware
{
    /**
     * Controller Context to use
     *
     * @var ControllerContext
     * @api
     */
    protected $controllerContext;

    /**
     * @param RenderingContextInterface $renderingContext
     */
    public function setRenderingContext(RenderingContextInterface $renderingContext)
    {
        parent::setRenderingContext($renderingContext);
        if ($renderingContext instanceof \TYPO3\CMS\Fluid\Core\Rendering\RenderingContext) {
            $this->controllerContext = $renderingContext->getControllerContext();
        }
    }
}
