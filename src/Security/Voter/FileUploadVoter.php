<?php

namespace AppBundle\Security\Voter;

use AppBundle\Documents\DocumentPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\UserDocument;

class FileUploadVoter extends AbstractAdherentVoter
{
    protected function supports($attribute, $type)
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
                return $adherent->isHost();
            case UserDocument::TYPE_EVENT:
                return $adherent->isReferent() || $adherent->isHost();
            case UserDocument::TYPE_ADHERENT_MESSAGE:
                return $adherent->isAdherentMessageRedactor();
            default:
                return false;
        }
    }
}
