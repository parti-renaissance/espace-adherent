<?php

namespace AppBundle\Form;

use AppBundle\Entity\ElectedRepresentative\LaREMSupportEnum;
use AppBundle\Entity\ElectedRepresentative\Mandate;
use AppBundle\Entity\ElectedRepresentative\MandateTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MandateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'placeholder' => '--',
                'choices' => MandateTypeEnum::CHOICES,
            ])
            ->add('politicalAffiliation', TextType::class, [
                'required' => false,
                'label' => 'Nuance politique',
            ])
            ->add('isElected', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => ['class' => 'form__checkbox form__checkbox--large'],
            ])
            ->add('laREMSupport', ChoiceType::class, [
                'required' => false,
                'label' => 'Soutien LaREM',
                'placeholder' => '--',
                'choices' => LaREMSupportEnum::toArray(),
                'choice_label' => function ($choice, $key) {
                    return 'elected_representative.mandate.larem_support.'.\mb_strtolower($key);
                },
            ])
            ->add('beginAt', 'sonata_type_date_picker', [
                'label' => 'Date de début de mandat',
            ])
            ->add('finishAt', 'sonata_type_date_picker', [
                'required' => false,
                'label' => 'Date de fin de mandat',
            ])
            ->add('geographicalArea', TextType::class, [
                'label' => 'Périmètre géographique',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (!isset($data['isElected']) || false === $data['isElected']) {
                unset($data['finishAt']);
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Mandate::class,
        ]);
    }
}
