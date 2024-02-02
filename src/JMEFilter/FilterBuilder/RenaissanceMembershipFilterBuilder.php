<?php

namespace App\JMEFilter\FilterBuilder;

use App\JMEFilter\FilterCollectionBuilder;
use App\JMEFilter\FilterGroup\MilitantFilterGroup;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use App\Scope\ScopeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class RenaissanceMembershipFilterBuilder implements FilterBuilderInterface
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function supports(string $scope, ?string $feature = null): bool
    {
        return \in_array($scope, ScopeEnum::ALL, true);
    }

    public function build(string $scope, ?string $feature = null): array
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

    public function getGroup(): string
    {
        return MilitantFilterGroup::class;
    }
}
