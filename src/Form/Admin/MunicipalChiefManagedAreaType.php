<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\MunicipalChiefManagedArea;
use AppBundle\Form\DataTransformer\StringToArrayTransformer;
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
            ->add('codes', TextType::class, [
                'label' => false,
            ])
            ->add('jecouteAccess', CheckboxType::class, [
                'required' => false,
                'label' => 'common.jecoute_manager',
            ])
        ;

        $builder
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                if ($data instanceof MunicipalChiefManagedArea && empty(array_filter($data->getCodes()))) {
                    $event->setData(null);
                }
            })
            ->get('codes')
            ->addModelTransformer(new StringToArrayTransformer())
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
