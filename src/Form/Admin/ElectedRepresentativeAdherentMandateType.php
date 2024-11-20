<?php

namespace App\Form\Admin;

use App\Admin\ElectedRepresentativeAdherentMandateAdmin;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Form\AdherentMandateType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectedRepresentativeAdherentMandateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mandateType', AdherentMandateType::class, [
                'label' => 'Type',
            ])
            ->add('zone', AdminZoneAutocompleteType::class, [
                'label' => 'Zone',
                'model_manager' => $options['model_manager'],
                'admin_code' => ElectedRepresentativeAdherentMandateAdmin::SERVICE_ID,
                'template' => 'admin/adherent/partial/elected_representative_adherent_mandate_autocomplete.html.twig',
                'minimum_input_length' => 1,
            ])
            ->add('delegation', TextType::class, [
                'label' => 'Délégation',
                'required' => false,
            ])
            ->add('beginAt', DatePickerType::class, [
                'label' => 'Date de début de mandat',
                'attr' => ['class' => 'width-140'],
            ])
            ->add('finishAt', DatePickerType::class, [
                'label' => 'Date de fin du mandat',
                'attr' => ['class' => 'width-140'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ElectedRepresentativeAdherentMandate::class,
            ])
            ->setRequired('model_manager')
            ->addAllowedTypes('model_manager', [ModelManagerInterface::class])
        ;
    }
}
