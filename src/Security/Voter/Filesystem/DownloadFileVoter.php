<?php

declare(strict_types=1);

namespace App\Security\Voter\Filesystem;

use App\Entity\Adherent;
use App\Entity\Filesystem\File;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Security\Voter\AbstractAdherentVoter;

class DownloadFileVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_DOWNLOAD_FILE';

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var File $subject */
        if (!$subject->isDisplayed()) {
            return false;
        }

        if ($subject->hasPermission(FilePermissionEnum::ALL)) {
            return true;
        }

        return \count(array_intersect($subject->getPermissionNames(), $adherent->getFilePermissions())) > 0;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof File;
    }
}
