<?php

namespace App\Form\ManagedUsers;

use App\Form\MyReferentCommitteeChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferentManagedUsersFilterType extends ManagedUsersFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('committee', MyReferentCommitteeChoiceType::class, ['required' => false]);

        if ($options['for_referent']) {
            $builder->add('isCertified', ChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'choices' => [
                    'common.all' => null,
                    'global.yes' => true,
                    'global.no' => false,
                ],
                'choice_value' => function ($choice) {
                    return false === $choice ? '0' : (string) $choice;
                },
            ]);
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
