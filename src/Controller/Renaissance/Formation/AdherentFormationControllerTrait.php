<?php

namespace App\Controller\Renaissance\Formation;

trait AdherentFormationControllerTrait
{
    public function checkAdherentFormationsEnabled(): void
    {
        if ('production' === $this->getParameter('app_environment')) {
            throw $this->createNotFoundException('Adherent formations are disabled.');
        }
    }
}
