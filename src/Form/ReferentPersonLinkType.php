<?php

namespace AppBundle\Form;

use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentPersonLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'form_full' => true,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'form_full' => true,
            ])
            ->add('email', TextType::class, [
                'label' => 'Mail',
                'form_full' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'form_full' => true,
            ])
            ->add('postalAddress', TextType::class, [
                'label' => 'Addresse postale',
                'form_full' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => false,
            'data_class' => ReferentPersonLink::class,
        ]);
    }
}
