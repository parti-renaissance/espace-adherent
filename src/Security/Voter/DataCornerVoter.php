<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\ScopeRepository;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DataCornerVoter extends Voter
{
    public const DATA_CORNER = 'DATA_CORNER';

    private ScopeRepository $scopeRepository;
    private GeneralScopeGenerator $scopeGenerator;

    public function __construct(ScopeRepository $scopeRepository, GeneralScopeGenerator $scopeGenerator)
    {
        $this->scopeRepository = $scopeRepository;
        $this->scopeGenerator = $scopeGenerator;
    }

    protected function supports($attribute, $subject)
    {
        return self::DATA_CORNER === $attribute;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (
            !$subject instanceof Adherent
            || !$codes = $this->scopeRepository->findCodesGrantedForDataCorner()
        ) {
            return false;
        }

        $adherentScopesCodes = array_map(function (Scope $scope) {
            return $scope->getMainCode();
        }, $this->scopeGenerator->generateScopes($subject));

        return !empty(array_intersect($codes, $adherentScopesCodes));
    }
}
