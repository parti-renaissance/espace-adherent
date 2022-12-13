<?php

namespace App\EntityListener;

use App\Entity\AdherentFormation\Formation;
use App\Entity\AdherentFormation\FormationContentTypeEnum;

class AdherentFormationListener
{
    public function prePersist(Formation $formation): void
    {
        $this->clearContentFields($formation);
    }

    public function preUpdate(Formation $formation): void
    {
        $this->clearContentFields($formation);
    }

    private function clearContentFields(Formation $formation): void
    {
        switch ($formation->getContentType()) {
            case FormationContentTypeEnum::FILE:
                $formation->setLink(null);
                break;
            case FormationContentTypeEnum::LINK:
                $formation->setFile(null);
                break;
        }
    }
}
