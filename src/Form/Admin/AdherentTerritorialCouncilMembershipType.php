<?php

namespace App\Form\Admin;

use App\Form\DataTransformer\AdminTerritorialCouncilAdherentMembershipDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AdherentTerritorialCouncilMembershipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(AdminTerritorialCouncilAdherentMembershipDataTransformer::REFERENT_TERRITORIAL_COUNCIL, TerritorialCouncilChoiceType::class, [
                'label' => 'territorial_council.referent',
            ])
            ->add(AdminTerritorialCouncilAdherentMembershipDataTransformer::LRE_MANAGER_TERRITORIAL_COUNCIL, TerritorialCouncilChoiceType::class, [
                'label' => 'territorial_council.lre_manager',
            ])
            ->add(AdminTerritorialCouncilAdherentMembershipDataTransformer::REFERENT_JAM_TERRITORIAL_COUNCIL, TerritorialCouncilChoiceType::class, [
                'label' => 'territorial_council.referent_jam',
            ])
            ->add(AdminTerritorialCouncilAdherentMembershipDataTransformer::GOVERNMENT_MEMBER_TERRITORIAL_COUNCIL, TerritorialCouncilChoiceType::class, [
                'label' => 'territorial_council.government_member',
            ])
            ->addModelTransformer(new AdminTerritorialCouncilAdherentMembershipDataTransformer())
        ;
    }
}
