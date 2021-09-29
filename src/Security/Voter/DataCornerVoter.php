<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\ScopeRepository;
use App\Scope\ScopeEnum;

class DataCornerVoter extends AbstractAdherentVoter
{
    public const DATA_CORNER = 'DATA_CORNER';

    private $scopeRepository;

    public function __construct(ScopeRepository $scopeRepository)
    {
        $this->scopeRepository = $scopeRepository;
    }

    protected function supports($attribute, $subject)
    {
        return self::DATA_CORNER === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $codes = $this->findCodesGrantedForDataCorner();

        return (\in_array(ScopeEnum::REFERENT, $codes) && ($adherent->isReferent() || $adherent->isDelegatedReferent()))
            || (\in_array(ScopeEnum::DEPUTY, $codes) && ($adherent->isDeputy() || $adherent->isDelegatedDeputy()))
            || (\in_array(ScopeEnum::CANDIDATE, $codes) && ($adherent->isHeadedRegionalCandidate() || $adherent->isDelegatedHeadedRegionalCandidate()))
            || (\in_array(ScopeEnum::SENATOR, $codes) && ($adherent->isSenator() || $adherent->isDelegatedSenator()))
            || (\in_array(ScopeEnum::NATIONAL, $codes) && $adherent->hasNationalRole())
        ;
    }

    private function findCodesGrantedForDataCorner(): array
    {
        return $this->scopeRepository->findCodesGrantedForDataCorner();
    }
}
