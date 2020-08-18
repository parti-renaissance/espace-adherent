<?php

namespace App\Mailchimp;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Request\EditCampaignRequest;
use Symfony\Component\EventDispatcher\Event;

class RequestEvent extends Event
{
    private $message;
    private $request;

    public function __construct(AdherentMessageInterface $message, EditCampaignRequest $request)
    {
        $this->message = $message;
        $this->request = $request;
    }

    public function getMessage(): AdherentMessageInterface
    {
        return $this->message;
    }

    public function getRequest(): EditCampaignRequest
    {
        return $this->request;
    }
}
