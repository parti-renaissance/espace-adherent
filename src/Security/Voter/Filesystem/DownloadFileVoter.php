<?php

namespace App\Security\Voter\Filesystem;

use App\Entity\Adherent;
use App\Entity\Filesystem\File;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Entity\MyTeam\DelegatedAccess;
use App\Security\Voter\AbstractAdherentVoter;
use Symfony\Component\HttpFoundation\RequestStack;

class DownloadFileVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_DOWNLOAD_FILE';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->requestStack->getSession()->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            $adherent = $delegatedAccess->getDelegator();
        }

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
