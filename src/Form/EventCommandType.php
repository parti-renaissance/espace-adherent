<?php

namespace App\Form;

use App\Event\EventCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventCommandType extends AbstractType
{
    public function getParent()
    {
        return BaseEventCommandType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EventGroupCategoryType::class, [
                'class' => $options['event_category_class'],
            ])
        ;
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
