<?php

namespace App\Form;

use App\Entity\ReferentTag;
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
                return $repository->createQueryBuilderWithReferentTagsCondition(
                    $this->getFilteredReferentTags($this->getReferentTags())
                );
            },
            'class' => PoliticalCommittee::class,
            'choice_label' => 'name',
        ]);
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    private function getFilteredReferentTags(array $referentTags): array
    {
        return array_filter($referentTags, function (ReferentTag $referentTag) {
            return !$referentTag->isCountryTag();
        });
    }
}
