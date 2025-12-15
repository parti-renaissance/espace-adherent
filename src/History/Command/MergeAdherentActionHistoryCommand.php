<?php

declare(strict_types=1);

namespace App\History\Command;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\AdministratorActionHistoryTypeEnum;

class MergeAdherentActionHistoryCommand extends AbstractAdministratorActionHistoryCommand
{
    public static function create(Administrator $administrator, Adherent $sourceAdherent, Adherent $targetAdherent): self
    {
        return new self(
            $administrator->getId(),
            AdministratorActionHistoryTypeEnum::ADHERENT_MERGE,
            [
                'adherent_source' => [
                    'id' => $sourceAdherent->getId(),
                    'email' => $sourceAdherent->getEmailAddress(),
                    'full_name' => $sourceAdherent->getFullName(),
                    'public_id' => $sourceAdherent->getPublicId(),
                    'tags' => $sourceAdherent->tags,
                ],
                'adherent_target' => [
                    'id' => $targetAdherent->getId(),
                    'email' => $targetAdherent->getEmailAddress(),
                    'full_name' => $targetAdherent->getFullName(),
                    'public_id' => $targetAdherent->getPublicId(),
                    'tags' => $targetAdherent->tags,
                ],
            ]
        );
    }
}
