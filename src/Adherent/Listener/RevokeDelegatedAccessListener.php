<?php

namespace App\Adherent\Listener;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RevokeDelegatedAccessListener implements EventSubscriberInterface
{
    private DelegatedAccessRepository $delegatedAccessRepository;

    private bool $isDeputy = false;

    public function __construct(DelegatedAccessRepository $delegatedAccessRepository)
    {
        $this->delegatedAccessRepository = $delegatedAccessRepository;
    }

    public function onBeforeUpdate(UserEvent $event): void
    {
        $adherent = $event->getUser();

        $this->isDeputy = $adherent->isDeputy();
    }

    public function onAfterUpdate(UserEvent $event)
    {
        $adherent = $event->getUser();

        if ($this->isDeputy && !$adherent->isDeputy()) {
            $this->delegatedAccessRepository->removeFromDelegator($adherent, AdherentSpaceEnum::DEPUTY);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_BEFORE_UPDATE => 'onBeforeUpdate',
            UserEvents::USER_UPDATED => 'onAfterUpdate',
        ];
    }
}
