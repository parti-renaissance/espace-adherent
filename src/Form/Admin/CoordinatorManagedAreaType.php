<?php

namespace App\Form\Admin;

use App\Entity\CoordinatorManagedArea;
use App\Form\DataTransformer\StringToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoordinatorManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codes', TextType::class, [
                'label' => false,
            ])
        ;

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if ($data instanceof CoordinatorManagedArea) {
                    if ([] === array_filter($data->getCodes())) {
                        $event->setData(null);
                    } else {
                        $data->setSector($event->getForm()->getConfig()->getOption('sector'));
                    }
                }
            })
            ->get('codes')->addModelTransformer(new StringToArrayTransformer())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('sector')
            ->setDefaults([
                'required' => false,
                'data_class' => CoordinatorManagedArea::class,
            ])
        ;
    }
}
