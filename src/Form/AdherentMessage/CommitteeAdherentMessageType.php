<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\AdherentMessage\CommitteeAdherentMessageDataObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeAdherentMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sendToTimeline', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeAdherentMessageDataObject::class,
        ]);
    }

    public function getParent()
    {
        return AdherentMessageType::class;
    }
}
