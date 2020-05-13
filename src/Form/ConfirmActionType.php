<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfirmActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deny', SubmitType::class, [
                'label' => 'global.no',
            ])
            ->add('allow', SubmitType::class, [
                'label' => 'global.yes',
            ])
        ;
    }
}
