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
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return DocumentPermissions::FILE_UPLOAD === $attribute;
    }

    /**
     * @param string $type
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $type): bool
    {
        switch ($type) {
            case UserDocument::TYPE_COMMITTEE_CONTACT:
            case UserDocument::TYPE_COMMITTEE_FEED:
            case UserDocument::TYPE_EVENT:
                return $adherent->isSupervisor() || $adherent->isHost();
            case UserDocument::TYPE_ADHERENT_MESSAGE:
                return $this->authorizationChecker->isGranted('ROLE_MESSAGE_REDACTOR');
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
