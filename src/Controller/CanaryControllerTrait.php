<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait CanaryControllerTrait
{
    /**
     * @throws NotFoundHttpException When the canary is not enabled
     */
    public function disableInProduction()
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }
    }
}
