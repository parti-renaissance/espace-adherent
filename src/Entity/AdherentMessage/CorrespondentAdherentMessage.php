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
        return ($this->getAuthor() ? trim($this->getAuthor()->getFirstName()) : null).$this->getFromNameSuffix();
    }

    protected function getFromNameSuffix(): string
    {
        return ' | Campagne 2022';
    }
}
