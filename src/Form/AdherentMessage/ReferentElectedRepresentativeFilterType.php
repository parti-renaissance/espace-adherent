<?php

namespace App\Form\AdherentMessage;

use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\UserListDefinitionEnum;
use App\Form\MyReferentTagChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentElectedRepresentativeFilterType extends AbstractType
{
    public function getParent(): ?string
    {
        return ElectedRepresentativeFilterType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (false === $options['single_zone']) {
            $builder->add('referentTag', MyReferentTagChoiceType::class);
        }

        $builder->remove('label');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => ReferentElectedRepresentativeFilter::class,
                'single_zone' => false,
                'user_list_types' => [UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE],
            ])
            ->setAllowedTypes('single_zone', ['bool'])
        ;
    }
}
