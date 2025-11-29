<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait CanaryControllerTrait
{
    abstract protected function getParameter(string $name);

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
