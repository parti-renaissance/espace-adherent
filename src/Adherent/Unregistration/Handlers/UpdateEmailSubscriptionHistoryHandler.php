<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\Entity\Adherent;
use AppBundle\History\EmailSubscriptionHistoryHandler;

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
