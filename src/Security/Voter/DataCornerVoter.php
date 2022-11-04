<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\ScopeRepository;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;

class DataCornerVoter extends AbstractAdherentVoter
{
    public const DATA_CORNER = 'DATA_CORNER';

    private ScopeRepository $scopeRepository;
    private GeneralScopeGenerator $scopeGenerator;

    public function __construct(ScopeRepository $scopeRepository, GeneralScopeGenerator $scopeGenerator)
    {
        $this->scopeRepository = $scopeRepository;
        $this->scopeGenerator = $scopeGenerator;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::DATA_CORNER === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$codes = $this->scopeRepository->findCodesGrantedForDataCorner()) {
            return false;
        }

        $adherentScopesCodes = array_map(function (Scope $scope) {
            return $scope->getMainCode();
        }, $this->scopeGenerator->generateScopes($adherent));

        return !empty(array_intersect($codes, $adherentScopesCodes));
    }
}
