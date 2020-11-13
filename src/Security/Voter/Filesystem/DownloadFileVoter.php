<?php

namespace App\Security\Voter\Filesystem;

use App\Entity\Adherent;
use App\Entity\Filesystem\File;
use App\Security\Voter\AbstractAdherentVoter;

class DownloadFileVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_DOWNLOAD_FILE';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$subject->isDisplayed()) {
            return false;
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof File;
    }
}
