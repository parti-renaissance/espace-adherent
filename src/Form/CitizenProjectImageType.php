<?php

namespace AppBundle\Form;

use AppBundle\ImageGenerator\Command\CitizenProjectImageCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitizenProjectImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('citizenProjectTitle')
            ->add('emoji', TextType::class)
            ->add('backgroundColor', ColorChoiceType::class, [
                'choices' => [
                    '#6f80ff',
                    '#185fca',
                    '#2abaff',
                    '#9ad7e8',
                    '#067065',
                    '#0cd283',
                    '#ffd400',
                    '#ff6955',
                    '#ff4863',
                    '#f8bcbc',
                    '#45e5ce',
                ],
            ])
            ->add('city')
            ->add('departmentCode')
            ->add('backgroundImage', FileType::class)
            ->add('preview', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            if ($form->get('preview')->isClicked()) {
                $form->add('download', SubmitType::class);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CitizenProjectImageCommand::class,
        ]);
    }
}
