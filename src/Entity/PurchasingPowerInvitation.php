<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Interactive\PurchasingPowerProcessor;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PurchasingPowerInvitationRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class PurchasingPowerInvitation extends InteractiveInvitation
{
    public static function createFromProcessor(PurchasingPowerProcessor $processor): self
    {
        $self = new self(Uuid::uuid4(), $processor->friendFirstName, $processor->friendAge, $processor->friendGender);

        $self->friendPosition = $processor->friendPosition->getContentKey();
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
