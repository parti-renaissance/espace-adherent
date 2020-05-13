<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\History\EmailSubscriptionHistoryHandler;

class UpdateEmailSubscriptionHistoryHandler implements UnregistrationAdherentHandlerInterface
{
    private $historyHandler;

    public function __construct(EmailSubscriptionHistoryHandler $historyHandler)
    {
        $this->historyHandler = $historyHandler;
    }

    public function supports(Adherent $adherent): bool
    {
        return !empty($adherent->getSubscriptionTypes());
    }

    public function handle(Adherent $adherent): void
    {
        $this->historyHandler->handleUnsubscriptions($adherent);
    }
}
