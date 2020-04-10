<?php

namespace AppBundle\Form\Admin;

use AppBundle\Donation\DonatorExtractCommand;
use AppBundle\Form\DataTransformer\StringToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonatorExtractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emails', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('fields', ChoiceType::class, [
                'choices' => DonatorExtractCommand::FIELD_CHOICES,
                'choice_label' => function (string $choice) {
                    return "donator.extract.field.$choice";
                },
                'required' => true,
                'expanded' => true,
                'multiple' => true,
            ])
        ;

        $builder
            ->get('emails')
            ->addModelTransformer(new StringToArrayTransformer(\PHP_EOL))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DonatorExtractCommand::class,
        ]);
    }
}
