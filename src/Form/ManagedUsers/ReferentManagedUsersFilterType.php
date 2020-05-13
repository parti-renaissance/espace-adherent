<?php

namespace App\Form\ManagedUsers;

use App\Form\MyReferentCommitteeChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ReferentManagedUsersFilterType extends ManagedUsersFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('committee', MyReferentCommitteeChoiceType::class, ['required' => false]);
    }
}
