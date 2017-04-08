<?php

namespace AppBundle\Controller\Traits;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait CanaryControllerTrait
{
    /**
     * @throws NotFoundHttpException When the canary is not enabled
     */
    public function disableInProduction()
    {
        /** @var Controller $this */
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }
    }
}
