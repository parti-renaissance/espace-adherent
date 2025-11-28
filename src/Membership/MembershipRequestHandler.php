<?php

declare(strict_types=1);

namespace App\Membership;

use App\Adherent\Unregistration\UnregistrationCommand;
use App\Adherent\UnregistrationHandler;
use App\Entity\Adherent;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipRequest\JeMengageMembershipRequest;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Referent\ReferentZoneManager;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $manager;
    private $referentZoneManager;
    private $unregistrationHandler;
    private $notifier;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        ObjectManager $manager,
        ReferentZoneManager $referentZoneManager,
        UnregistrationHandler $unregistrationHandler,
        MembershipNotifier $notifier,
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->referentZoneManager = $referentZoneManager;
        $this->unregistrationHandler = $unregistrationHandler;
        $this->notifier = $notifier;
    }

    public function initialiseMembershipRequest(string $source): MembershipInterface
    {
        return new JeMengageMembershipRequest();
    }

    public function createAdherent(MembershipInterface $membershipRequest): Adherent
    {
        $this->manager->persist($adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest));

        $this->referentZoneManager->assignZone($adherent);

        $this->manager->flush();

        $this->dispatcher->dispatch(new UserEvent(
            $adherent,
            $membershipRequest->allowEmailNotifications,
            $membershipRequest->allowMobileNotifications
        ), UserEvents::USER_CREATED);

        return $adherent;
    }

    public function terminateMembership(
        Adherent $adherent,
        ?UnregistrationCommand $command = null,
        bool $sendMail = true,
        ?string $comment = null,
    ): void {
        $this->unregistrationHandler->handle($adherent, $command, $comment);

        if ($sendMail || ($command && $command->getNotification())) {
            $this->notifier->sendUnregistrationMessage($adherent);
        }

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_DELETED);
    }
}
