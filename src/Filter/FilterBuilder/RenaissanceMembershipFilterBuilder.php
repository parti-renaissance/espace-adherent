<?php

namespace App\Filter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use App\Scope\FeatureEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class RenaissanceMembershipFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, string $feature = null): bool
    {
        return FeatureEnum::ELECTED_REPRESENTATIVE !== $feature;
    }

    public function build(string $scope, string $feature = null): array
    {
        return (new FilterCollectionBuilder())
            ->createSelect('renaissance_membership', 'Renaissance')
            ->setChoices($this->getTranslatedChoices())
            ->getFilters()
        ;
    }

    public function getTranslatedChoices(): array
    {
        $choices = [];
        foreach (RenaissanceMembershipFilterEnum::CHOICES as $transKey => $choice) {
            $choices[$choice] = $this->translator->trans($transKey);
        }

        return $choices;
    }
}
