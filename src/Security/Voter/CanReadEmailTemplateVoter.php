<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Email\EmailTemplate;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;

class CanReadEmailTemplateVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_READ_EMAIL_TEMPLATE';

    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly Security $security,
    ) {
    }

    /**
     * @param EmailTemplate $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $scope = $this->scopeGeneratorResolver->generate();
        $user = $scope && $scope->getDelegatedAccess() ? $scope->getDelegator() : $this->security->getUser();

        if (!$scope) {
            return false;
        }

        if ($subject->isStatutory && !$scope->hasFeature(FeatureEnum::STATUTORY_MESSAGE)) {
            return false;
        }

        if (
            ($subject->getScopes() && \in_array($scope->getMainCode(), $subject->getScopes(), true))
            && ($subject->getZones()->isEmpty() || 0 !== \count(array_intersect($scope->getZones(), $subject->getZones()->toArray())))
        ) {
            return true;
        }

        if ($subject->getCreatedByAdherent() && $subject->getCreatedByAdherent() === $user) {
            return true;
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof EmailTemplate;
    }
}
