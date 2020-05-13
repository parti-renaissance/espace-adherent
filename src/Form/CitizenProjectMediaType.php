<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class CitizenProjectMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('citizenProjectTitle')
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
            ->add('backgroundImage', FileType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return 'citizen_project_media';
    }
}
