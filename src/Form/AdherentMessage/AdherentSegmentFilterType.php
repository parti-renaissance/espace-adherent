<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\Entity\AdherentMessage\Filter\AdherentSegmentAwareFilterInterface;
use AppBundle\Form\AdherentSegmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentSegmentFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('adherentSegment', AdherentSegmentType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdherentSegmentAwareFilterInterface::class,
        ]);
    }
}
