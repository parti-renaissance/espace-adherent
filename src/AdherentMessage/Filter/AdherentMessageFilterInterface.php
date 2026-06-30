<?php

declare(strict_types=1);

namespace App\AdherentMessage\Filter;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Segment\AudienceSegment;

interface AdherentMessageFilterInterface
{
    public function getMessage(): ?AdherentMessageInterface;

    public function setMessage(AdherentMessageInterface $message): void;

    public function getSegment(): ?AudienceSegment;

    public function setSegment(?AudienceSegment $segment): void;
}
