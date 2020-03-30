<?php

namespace AppBundle\Admin\ElectedRepresentative;

use AppBundle\Entity\ElectedRepresentative\ElectedRepresentativeLabel;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ElectedRepresentativeLabelAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['create', 'edit', 'delete']);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Saisissez le nom d\'une étiquette',
                ],
            ])
            ->add('onGoing', CheckboxType::class, [
                'required' => false,
                'label' => 'En cours',
                'attr' => [
                    'class' => 'on-going',
                ],
            ])
            ->add('beginYear', ChoiceType::class, [
                'label' => 'Date de début',
                'placeholder' => '--',
                'choices' => ElectedRepresentativeLabel::getYears(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ])
            ->add('finishYear', ChoiceType::class, [
                'required' => false,
                'label' => 'Date de fin',
                'placeholder' => '--',
                'choices' => ElectedRepresentativeLabel::getYears(),
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ])
        ;

        $formMapper->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if ((isset($data['onGoing']) && '1' === $data['onGoing'])) {
                unset($data['finishYear']);
                $event->setData($data);
            }
        });
    }
}
