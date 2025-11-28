<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BaseFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom du fichier',
            ])
            ->add('file', FileType::class, [
                'label' => 'Fichier',
            ])
        ;

        if (!$options['can_update_file']) {
            $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'changeRequiredOptionForFile']);
        }
    }

    public function changeRequiredOptionForFile(FormEvent $formEvent): void
    {
        /** @var BaseFile $file */
        $file = $formEvent->getData();

        if ($file && $file->getPath()) {
            $formEvent->getForm()
                ->remove('file')
                ->add('path', TextType::class, [
                    'disabled' => true,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'can_update_file' => false,
        ]);
    }
}
