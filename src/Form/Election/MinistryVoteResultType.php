<?php

namespace App\Form\Election;

use App\Entity\Election\MinistryVoteResult;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MinistryVoteResultType extends AbstractType
{
    public function getParent()
    {
        return BaseVoteResultType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('listTotalResults', CollectionType::class, [
                'entry_type' => BasicVoteListResultType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MinistryVoteResult::class,
        ]);
    }
}
