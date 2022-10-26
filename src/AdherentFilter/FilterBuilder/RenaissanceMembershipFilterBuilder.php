<?php

namespace App\AdherentFilter\FilterBuilder;

use App\Filter\FilterCollectionBuilder;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class RenaissanceMembershipFilterBuilder implements AdherentFilterBuilderInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function supports(string $scope, string $feature = null): bool
    {
        return true;
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
