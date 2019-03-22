<?php

namespace AppBundle\Form;

use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentMessageReferentZoneFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('referentTag', ReferentTagChoiceType::class, [
            'expanded' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdherentZoneFilter::class,
        ]);
    }
}
