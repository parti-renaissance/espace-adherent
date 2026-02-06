<?php

declare(strict_types=1);

namespace App\Form\Procuration;

use App\Procuration\Command\RequestCommand;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestType extends AbstractProcurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('fromFrance', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => RequestCommand::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'procuration_request';
    }
}
