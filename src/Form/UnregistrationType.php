<?php

declare(strict_types=1);

namespace App\Form;

use App\Adherent\Unregistration\UnregistrationCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnregistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['is_renaissance']) {
            $builder
                ->add('comment', TextareaType::class, [
                    'required' => false,
                ])
            ;

            if (!$options['is_admin']) {
                $builder
                    ->add('reasons', ChoiceType::class, [
                        'choices' => $options['reasons'],
                        'choice_translation_domain' => 'forms',
                        'expanded' => true,
                        'multiple' => true,
                    ])
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => UnregistrationCommand::class,
                'reasons' => [],
                'is_renaissance' => false,
                'is_admin' => false,
                'validation_groups' => function (Options $options) {
                    if ($options['is_renaissance']) {
                        return ['renaissance'];
                    }

                    if ($options['is_admin']) {
                        return ['admin'];
                    }

                    return ['Default'];
                },
            ])
            ->setAllowedTypes('is_renaissance', 'bool')
            ->setAllowedTypes('is_admin', 'bool')
        ;
    }
}
