<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait CanaryControllerTrait
{
    abstract protected function getParameter($name);

    /**
     * @throws NotFoundHttpException When the canary is not enabled
     */
    public function disableInProduction(): void
    {
        if (!((bool) $this->getParameter('enable_canary')) && !$this->isGranted('ROLE_CANARY_TESTER')) {
            throw $this->createNotFoundException();
        }
    }
}
