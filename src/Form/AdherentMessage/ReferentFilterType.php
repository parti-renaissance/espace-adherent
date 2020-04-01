<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Form\MyReferentTagChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentFilterType extends AbstractType
{
    public function getParent()
    {
        return AdherentZoneFilterType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contactOnlyVolunteers', CheckboxType::class, ['required' => false]);

        if (false === $options['is_referent_from_paris']) {
            $builder->add('contactOnlyRunningMates', CheckboxType::class, ['required' => false]);
        }

        if (false === $options['single_zone']) {
            $builder->add('referentTags', MyReferentTagChoiceType::class, ['multiple' => true]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ReferentUserFilter::class,
                'single_zone' => false,
                'is_referent_from_paris' => false,
            ])
            ->setAllowedTypes('single_zone', ['bool'])
            ->setAllowedTypes('is_referent_from_paris', ['bool'])
        ;
    }
}
