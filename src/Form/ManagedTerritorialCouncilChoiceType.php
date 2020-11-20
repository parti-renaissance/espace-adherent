<?php

namespace App\Form;

use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManagedTerritorialCouncilChoiceType extends AbstractConnectedUserFormType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'query_builder' => function (TerritorialCouncilRepository $repository) {
                return $repository->createQueryBuilderWithReferentTagsCondition(
                    $this->getFilteredReferentTags($this->getReferentTags())
                );
            },
            'class' => TerritorialCouncil::class,
            'choice_label' => 'name',
        ]);
    }

    public function getParent()
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
