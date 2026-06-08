<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait CanaryControllerTrait
{
    abstract protected function getParameter(string $name);

    protected function isCanaryEnabled(): bool
    {
        return (bool) $this->getParameter('enable_canary') || $this->isGranted('ROLE_CANARY_TESTER');
    }

    /**
     * @throws NotFoundHttpException When the canary is not enabled
     */
    public function disableInProduction(): void
    {
        if (!$this->isCanaryEnabled()) {
            throw $this->createNotFoundException();
        }
    }
}
