<?php

namespace App\Security\Voter;

use App\Documents\DocumentPermissions;
use App\Entity\Adherent;
use App\Entity\UserDocument;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FileUploadVoter extends AbstractAdherentVoter
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    protected function supports($attribute, $subject)
    {
        return DocumentPermissions::FILE_UPLOAD === $attribute;
    }

    /**
     * @param string $type
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $type): bool
    {
        switch ($type) {
            case UserDocument::TYPE_REFERENT:
                return $adherent->isReferent();
            case UserDocument::TYPE_COMMITTEE_CONTACT:
            case UserDocument::TYPE_COMMITTEE_FEED:
                return $adherent->isSupervisor() || $adherent->isHost();
            case UserDocument::TYPE_EVENT:
                return $adherent->isReferent() || $adherent->isSupervisor() || $adherent->isHost();
            case UserDocument::TYPE_ADHERENT_MESSAGE:
                return $this->authorizationChecker->isGranted('ROLE_MESSAGE_REDACTOR');
            case UserDocument::TYPE_TERRITORIAL_COUNCIL_FEED:
                return $adherent->isTerritorialCouncilPresident();
            case UserDocument::TYPE_NEWS:
                if (!$scope = $this->scopeGeneratorResolver->generate()) {
                    return false;
                }

                return $scope->hasFeature(FeatureEnum::NEWS);
            default:
                return false;
        }
    }
}
