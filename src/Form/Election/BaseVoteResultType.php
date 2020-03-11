<?php

namespace AppBundle\Form\Election;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class BaseVoteResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registered', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('abstentions', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('participated', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('expressed', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
        ;
    }
}
