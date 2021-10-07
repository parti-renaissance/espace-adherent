<?php

namespace App\Adherent\Command;

use App\Entity\Adherent;

class SendResubscribeEmailCommand
{
    private Adherent $adherent;
    private string $triggerSource;

    public function __construct(Adherent $adherent, string $triggerSource)
    {
        $this->adherent = $adherent;
        $this->triggerSource = $triggerSource;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getTriggerSource(): string
    {
        return $this->triggerSource;
    }
}
