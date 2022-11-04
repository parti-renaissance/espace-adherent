<?php

namespace App\Form\Election;

use App\Entity\Election\MinistryListTotalResult;
use App\Form\VoteResultListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicVoteListResultType extends AbstractType
{
    public function getParent(): ?string
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
