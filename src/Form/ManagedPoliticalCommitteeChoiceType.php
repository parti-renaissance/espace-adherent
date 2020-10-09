<?php

namespace App\Form;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Repository\TerritorialCouncil\PoliticalCommitteeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedPoliticalCommitteeChoiceType extends AbstractConnectedUserFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'query_builder' => function (PoliticalCommitteeRepository $repository) {
                return $repository->createQueryBuilderWithReferentTagsCondition($this->getReferentTags());
            },
            'class' => PoliticalCommittee::class,
            'choice_label' => 'name',
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
