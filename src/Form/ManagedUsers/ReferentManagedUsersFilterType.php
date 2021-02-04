<?php

namespace App\Form\ManagedUsers;

use App\Form\BooleanChoiceType;
use App\Form\MyReferentCommitteeChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentManagedUsersFilterType extends ManagedUsersFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('committee', MyReferentCommitteeChoiceType::class, ['required' => false]);

        if ($options['for_referent']) {
            $builder->add('isCertified', BooleanChoiceType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'for_referent' => false,
            ])
            ->setAllowedTypes('for_referent', ['bool'])
        ;
    }
}
