<?php

namespace App\Entity;

use App\Interactive\MyEuropeProcessor;
use App\Repository\MyEuropeInvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: MyEuropeInvitationRepository::class)]
class MyEuropeInvitation extends InteractiveInvitation
{
    public static function createFromProcessor(MyEuropeProcessor $processor): self
    {
        $self = new self(Uuid::uuid4(), $processor->friendFirstName, $processor->friendAge, $processor->friendGender);

        $self->friendPosition = $processor->friendPosition?->getContentKey();
        $self->authorFirstName = $processor->selfFirstName;
        $self->authorLastName = $processor->selfLastName;
        $self->authorEmailAddress = $processor->selfEmail;
        $self->friendEmailAddress = $processor->friendEmail;
        $self->mailSubject = $processor->messageSubject;
        $self->mailBody = $processor->messageContent;

        $processor->defineChoices($self->choices);

        return $self;
    }
}
