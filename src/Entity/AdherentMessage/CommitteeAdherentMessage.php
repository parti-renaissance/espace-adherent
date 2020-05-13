<?php

namespace App\Entity\AdherentMessage;

use App\AdherentMessage\AdherentMessageDataObject;
use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\CommitteeAdherentMessageDataObject;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommitteeAdherentMessage extends AbstractAdherentMessage
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $sendToTimeline = false;

    public function isSendToTimeline(): bool
    {
        return $this->sendToTimeline;
    }

    public function getType(): string
    {
        return AdherentMessageTypeEnum::COMMITTEE;
    }

    public function updateFromDataObject(AdherentMessageDataObject $dataObject): AdherentMessageInterface
    {
        parent::updateFromDataObject($dataObject);

        if ($dataObject instanceof CommitteeAdherentMessageDataObject) {
            $this->sendToTimeline = $dataObject->isSendToTimeline();
        }

        return $this;
    }
}
