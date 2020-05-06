<?php

namespace App\Form\Election;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class VoteResultWithListsType extends AbstractType
{
    public function getParent()
    {
        return BaseVoteResultType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('listTotalResults', CollectionType::class, [
                'entry_type' => ListTotalResultType::class,
                'allow_add' => false,
                'allow_delete' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
        ;
    }
}
