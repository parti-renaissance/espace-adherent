<?php

namespace App\Security;

use App\Adherent\LastLoginGroupEnum;
use App\Entity\Adherent;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AdherentLoginGroupRecorder implements EventSubscriberInterface
{
    private ObjectManager $manager;
    private MessageBusInterface $bus;

    public function __construct(ObjectManager $manager, MessageBusInterface $bus)
    {
        $this->manager = $manager;
        $this->bus = $bus;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();

        $user = $token->getUser();
        if (!$user instanceof Adherent) {
            return;
        }

        $user->setLastLoginGroup(LastLoginGroupEnum::LESS_THAN_1_MONTH);
        $this->manager->flush();

        $this->bus->dispatch(new AdherentChangeCommand(
            $user->getUuid(),
            $user->getEmailAddress()
        ));
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }
}
