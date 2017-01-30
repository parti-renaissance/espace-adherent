<?php

namespace AppBundle\Form;

use AppBundle\Committee\Event\EventCategories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventCategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => EventCategories::CHOICES,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
