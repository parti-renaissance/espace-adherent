<?php

declare(strict_types=1);

namespace App\Form\Admin\Filesystem;

use App\Entity\Filesystem\FilePermission;
use App\Entity\Filesystem\FilePermissionEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilePermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', ChoiceType::class, [
                'label' => false,
                'choices' => FilePermissionEnum::toArray(),
                'choice_label' => function (string $choice) {
                    return "file_permission.$choice";
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'required' => true,
                'data_class' => FilePermission::class,
            ])
        ;
    }
}
