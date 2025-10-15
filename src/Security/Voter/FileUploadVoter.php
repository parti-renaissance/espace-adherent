<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\UserDocument;
use App\Scope\FeatureEnum;
use App\Scope\ScopeGeneratorResolver;

class FileUploadVoter extends AbstractAdherentVoter
{
    public const FILE_UPLOAD = 'FILE_UPLOAD';

    private const TYPE_FEATURE_MAPPING = [
        UserDocument::TYPE_PUBLICATION => FeatureEnum::PUBLICATIONS,
    ];

    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::FILE_UPLOAD === $attribute;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (\in_array($subject, [UserDocument::TYPE_PUBLICATION, UserDocument::TYPE_NEWS, UserDocument::TYPE_ADHERENT_MESSAGE], true)) {
            if (!$scope = $this->scopeGeneratorResolver->generate()) {
                return false;
            }

            return $scope->hasFeature(FeatureEnum::NEWS);
        }

        switch ($subject) {
            case UserDocument::TYPE_COMMITTEE_CONTACT:
            case UserDocument::TYPE_COMMITTEE_FEED:
            case UserDocument::TYPE_EVENT:
                return $adherent->isSupervisor() || $adherent->isHost();
        }

        return false;
    }
}
