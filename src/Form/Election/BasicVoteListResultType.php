<?php

namespace AppBundle\Form\Election;

use AppBundle\Entity\Election\MinistryListTotalResult;
use AppBundle\Form\VoteResultListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicVoteListResultType extends AbstractType
{
    public function getParent()
    {
        return VoteResultListType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('total', IntegerType::class, [
                'attr' => ['min' => 0],
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MinistryListTotalResult::class,
        ]);
    }
}
