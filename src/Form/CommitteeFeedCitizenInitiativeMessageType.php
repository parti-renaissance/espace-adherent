<?php

namespace AppBundle\Form;

use AppBundle\Committee\Feed\CommitteeCitizenInitiativeMessage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFeedCitizenInitiativeMessageType extends CommitteeFeedMessageType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CommitteeCitizenInitiativeMessage::class);
    }
}
