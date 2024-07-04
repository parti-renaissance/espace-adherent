<?php

namespace App\Admin\Election;

use App\Form\TelNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CityCardContactAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('phone', TelNumberType::class, [
                'required' => false,
            ])
            ->add('caller', TextType::class, [
                'label' => 'Qui appelle?',
                'required' => false,
            ])
            ->add('done', CheckboxType::class, [
                'label' => 'Fait',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
            ])
        ;
    }
}
