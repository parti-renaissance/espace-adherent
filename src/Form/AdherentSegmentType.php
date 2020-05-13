<?php

namespace App\Form;

use App\Entity\AdherentSegment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentSegmentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'placeholder' => 'Choisissez une liste de diffusion',
            'class' => AdherentSegment::class,
            'choice_value' => 'uuid',
            'choice_label' => static function (AdherentSegment $segment) {
                if (!$segment->isSynchronized()) {
                    return $segment->getLabel().' (indisponible, en cours de préparation...)';
                }

                return $segment->getLabel();
            },
            'choice_attr' => static function (AdherentSegment $segment) {
                if (!$segment->isSynchronized()) {
                    return ['disabled' => 'disabled'];
                }

                return [];
            },
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
