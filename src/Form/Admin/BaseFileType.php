<?php

namespace App\Form\Admin;

use App\Entity\BaseFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BaseFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('file', FileType::class)
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'changeRequiredOptionForFile']);
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
}
