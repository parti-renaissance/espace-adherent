<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;

class EventInvitationType extends AbstractType
{
    public function getParent(): ?string
    {
        return BaseEventInvitationType::class;
    }
}
