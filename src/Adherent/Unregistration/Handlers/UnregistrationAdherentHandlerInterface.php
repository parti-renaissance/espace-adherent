<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\Entity\Adherent;

interface UnregistrationAdherentHandlerInterface
{
    public function supports(Adherent $adherent): bool;

    public function handle(Adherent $adherent): void;
}
