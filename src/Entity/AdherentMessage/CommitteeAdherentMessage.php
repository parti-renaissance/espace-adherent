<?php

namespace AppBundle\Entity\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\CommitteeAdherentMessageDataObject;
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

    public function hasReadOnlyFilter(): bool
    {
        return true;
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
