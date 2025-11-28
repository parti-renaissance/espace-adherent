<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\AdherentFormation\Formation;

class AdherentFormationListener
{
    public function prePersist(Formation $formation): void
    {
        $this->handle($formation);
    }

    public function preUpdate(Formation $formation): void
    {
        $this->handle($formation);
    }

    public function handle(Formation $formation): void
    {
        $this->clearContentFields($formation);
        $this->checkIfValid($formation);
    }

    private function clearContentFields(Formation $formation): void
    {
        if ($formation->isFileContent()) {
            $formation->setLink(null);
        }

        if ($formation->isLinkContent()) {
            $formation->setFilePath(null);
        }
    }

    private function checkIfValid(Formation $formation): void
    {
        $formation->setValid(
            ($formation->isFileContent() && $formation->getFilePath())
            || ($formation->isLinkContent() && $formation->getLink())
        );
    }
}
