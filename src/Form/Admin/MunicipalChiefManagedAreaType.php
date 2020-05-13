<?php

namespace App\Form\Admin;

use App\Entity\MunicipalChiefManagedArea;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MunicipalChiefManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inseeCode', TextType::class, [
                'label' => false,
            ])
            ->add('jecouteAccess', CheckboxType::class, [
                'required' => false,
                'label' => 'common.jecoute_manager',
            ])
        ;

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var MunicipalChiefManagedArea $data */
                $data = $event->getData();

                if ($data instanceof MunicipalChiefManagedArea && !$data->getInseeCode()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => false,
            'data_class' => MunicipalChiefManagedArea::class,
        ]);
    }
}
