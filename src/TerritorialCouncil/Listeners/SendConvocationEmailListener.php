<?php

namespace App\TerritorialCouncil\Listeners;

use App\Mailer\MailerService;
use App\Mailer\Message\TerritorialCouncilElectionConvocationMessage;
use App\TerritorialCouncil\Event\TerritorialCouncilEvent;
use App\TerritorialCouncil\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendConvocationEmailListener implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerService $transactionalMailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::DESIGNATION_SWITCHED => 'onDesignationSwitch',
        ];
    }

    public function onDesignationSwitch(TerritorialCouncilEvent $event): void
    {
        $territorialCouncil = $event->getTerritorialCouncil();

        $membershipCollection = $territorialCouncil->getMemberships();

        $this->mailer->sendMessage(TerritorialCouncilElectionConvocationMessage::create(
            $territorialCouncil,
            $membershipCollection->toArray(),
            $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $membershipCollection->getPresident()
        ));
    }
}
