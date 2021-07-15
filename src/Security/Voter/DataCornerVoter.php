<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Scope;
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
        foreach ($this->findScopesGrantedForDataCorner() as $scope) {
            switch ($scope->getCode()) {
                case ScopeEnum::REFERENT:
                    if ($adherent->isReferent()) {
                        return true;
                    }

                    break;
                case ScopeEnum::DEPUTY:
                    if ($adherent->isDeputy()) {
                        return true;
                    }

                    break;
                case ScopeEnum::CANDIDATE:
                    if ($adherent->isHeadedRegionalCandidate()) {
                        return true;
                    }

                    break;
                case ScopeEnum::SENATOR:
                    if ($adherent->isSenator()) {
                        return true;
                    }

                    break;
                case ScopeEnum::NATIONAL:
                    if ($adherent->hasNationalRole()) {
                        return true;
                    }

                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Scope entity with code "%s" has not been defined in the "%s" voter.', $scope->getCode(), self::class));
            }
        }

        return false;
    }

    /**
     * @return Scope[]|array
     */
    private function findScopesGrantedForDataCorner(): array
    {
        return $this->scopeRepository->findGrantedForDataCorner();
    }
}
