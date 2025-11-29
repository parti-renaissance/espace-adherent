<?php

declare(strict_types=1);

namespace App\Form;

use App\Address\AddressInterface;
use App\Form\DataTransformer\PhoneNumberToArrayTransformer;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TelNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $phoneTransformer = current($builder->getViewTransformers());
        $builder->resetViewTransformers()->addViewTransformer(new PhoneNumberToArrayTransformer($phoneTransformer));
    }

    public function getParent(): string
    {
        return PhoneNumberType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Téléphone',
            'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
            'default_region' => AddressInterface::FRANCE,
            'preferred_country_choices' => [AddressInterface::FRANCE],
        ]);
    }
}
