<?php

namespace App\Form\Admin\Membership;

use App\Address\Address;
use App\Entity\Adherent;
use App\Form\CivilityType;
use App\Form\PostAddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gender', CivilityType::class)
            ->add('firstName')
            ->add('lastName')
            ->add('nationality', CountryType::class, [
                'preferred_choices' => [Address::FRANCE],
            ])
            ->add('postAddress', PostAddressType::class)
            ->add('emailAddress')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Adherent::class,
            'validation_groups' => ['admin_adherent_renaissance_create'],
        ]);
    }
}
