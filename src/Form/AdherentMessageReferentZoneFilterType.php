<?php

namespace AppBundle\Form;

use AppBundle\Entity\AdherentMessage\Filter\ReferentZoneFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentMessageReferentZoneFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('referentTags', ReferentTagChoiceType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ReferentZoneFilter::class,
        ]);
    }
}
