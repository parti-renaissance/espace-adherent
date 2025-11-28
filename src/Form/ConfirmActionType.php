<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfirmActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('allow', SubmitType::class, [
            'label' => $options['allow_label'],
        ]);

        if ($options['with_deny']) {
            $builder->add('deny', SubmitType::class, [
                'label' => $options['deny_label'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'with_deny' => true,
                'deny_label' => 'global.no',
                'allow_label' => 'global.yes',
            ])
            ->setDefined(['with_deny', 'deny_label', 'allow_label'])
            ->setAllowedTypes('with_deny', 'bool')
            ->setAllowedTypes('deny_label', 'string')
            ->setAllowedTypes('allow_label', 'string')
        ;
    }
}
