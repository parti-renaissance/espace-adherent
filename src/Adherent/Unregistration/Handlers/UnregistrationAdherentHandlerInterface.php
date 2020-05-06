<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;

interface UnregistrationAdherentHandlerInterface
{
    public function supports(Adherent $adherent): bool;

    public function handle(Adherent $adherent): void;
}
