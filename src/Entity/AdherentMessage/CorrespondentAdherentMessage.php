<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CorrespondentAdherentMessage extends AbstractAdherentMessage implements CampaignAdherentMessageInterface
{
    public function getType(): string
    {
        return AdherentMessageTypeEnum::CORRESPONDENT;
    }

    protected function getScope(): ?string
    {
        return ScopeEnum::CORRESPONDENT;
    }

    public function getFromName(): ?string
    {
        if ($this->getSender()) {
            return trim($this->getSender()->getFirstName()).$this->getFromNameSuffix();
        }

        if ($this->author) {
            return trim($this->author->getFirstName()).$this->getFromNameSuffix();
        }

        return null;
    }

    protected function getFromNameSuffix(): string
    {
        return ' | Campagne 2022';
    }
}
