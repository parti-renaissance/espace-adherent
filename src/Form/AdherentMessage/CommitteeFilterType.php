<?php

namespace AppBundle\Form\AdherentMessage;

use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Form\MyCommitteeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('committee', MyCommitteeChoiceType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeFilter::class,
        ]);
    }
}
