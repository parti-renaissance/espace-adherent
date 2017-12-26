<?php

namespace AppBundle\Form;

use AppBundle\Event\EventCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventCommandType extends AbstractType
{
    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => EventCommand::class,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'committee_event';
    }
}
