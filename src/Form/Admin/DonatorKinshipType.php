<?php

namespace App\Form\Admin;

use App\Entity\Donator;
use App\Entity\DonatorKinship;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonatorKinshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('related', ModelAutocompleteType::class, [
                'label' => 'Donateur',
                'minimum_input_length' => 1,
                'items_per_page' => 20,
                'property' => [
                    'identifier',
                    'firstName',
                    'lastName',
                    'emailAddress',
                ],
                'model_manager' => $options['model_manager'],
                'class' => Donator::class,
                'admin_code' => 'app.admin.donation',
                'req_params' => [
                    'field' => 'donator',
                ],
                'btn_add' => false,
            ])
            ->add('kinship', TextType::class, [
                'label' => 'Lien de parenté',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => DonatorKinship::class,
            ])
            ->setDefined('model_manager')
            ->setAllowedTypes('model_manager', ModelManagerInterface::class)
        ;
    }
}
