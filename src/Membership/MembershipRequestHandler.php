<?php

namespace AppBundle\Membership;

use AppBundle\Address\PostAddressFactory;
use AppBundle\CitizenInitiative\ActivitySubscriptionManager;
use AppBundle\CitizenInitiative\CitizenInitiativeManager;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Summary;
use AppBundle\Event\EventManager;
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentAccountActivationMessage;
use AppBundle\Mailer\Message\AdherentTerminateMembershipMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $addressFactory;
    private $urlGenerator;
    private $mailer;
    private $manager;
    private $committeeManager;
    private $registrationManager;
    private $citizenInitiativeManager;
    private $eventManager;
    private $committeeFeedManager;
    private $activitySubscriptionManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        UrlGeneratorInterface $urlGenerator,
        MailerService $mailer,
        ObjectManager $manager,
        CommitteeManager $committeeManager,
        EventRegistrationManager $registrationManager,
        CitizenInitiativeManager $citizenInitiativeManager,
        EventManager $eventManager,
        CommitteeFeedManager $committeeFeedManager,
        ActivitySubscriptionManager $activitySubscriptionManager
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->committeeManager = $committeeManager;
        $this->registrationManager = $registrationManager;
        $this->citizenInitiativeManager = $citizenInitiativeManager;
        $this->committeeFeedManager = $committeeFeedManager;
        $this->eventManager = $eventManager;
        $this->activitySubscriptionManager = $activitySubscriptionManager;
    }

    public function handle(MembershipRequest $membershipRequest)
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $token = AdherentActivationToken::generate($adherent);

        $this->manager->persist($adherent);
        $this->manager->persist($token);
        $this->manager->flush();

        $activationUrl = $this->generateMembershipActivationUrl($adherent, $token);
        $this->mailer->sendMessage(AdherentAccountActivationMessage::createFromAdherent($adherent, $activationUrl));

        $this->dispatcher->dispatch(AdherentEvents::REGISTRATION_COMPLETED, new AdherentAccountWasCreatedEvent($adherent, $membershipRequest));
    }

    public function update(Adherent $adherent, MembershipRequest $membershipRequest)
    {
        $adherent->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));

        $this->dispatcher->dispatch(AdherentEvents::PROFILE_UPDATED, new AdherentProfileWasUpdatedEvent($adherent));

        $this->manager->flush();
    }

    private function generateMembershipActivationUrl(Adherent $adherent, AdherentActivationToken $token)
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'activation_token' => (string) $token->getValue(),
        ];

        return $this->urlGenerator->generate('app_membership_activate', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function terminateMembership(UnregistrationCommand $command, Adherent $adherent)
    {
        $unregistrationFactory = new UnregistrationFactory();
        $unregistration = $unregistrationFactory->createFromUnregistrationCommandAndAdherent($command, $adherent);

        $this->manager->persist($unregistration);

        $message = AdherentTerminateMembershipMessage::createFromAdherent($adherent);
        $token = $this->manager->getRepository(AdherentActivationToken::class)->findOneBy(['adherentUuid' => $adherent->getUuid()->toString()]);
        $summary = $this->manager->getRepository(Summary::class)->findOneForAdherent($adherent);

        $this->removeAdherentMemberShips($adherent);
        $this->citizenInitiativeManager->removeOrganizerCitizenInitiatives($adherent);
        $this->eventManager->removeOrganizerEvents($adherent);
        $this->registrationManager->anonymizeAdherentRegistrations($adherent);
        $this->committeeFeedManager->removeAuthorItems($adherent);
        $this->activitySubscriptionManager->removeAdherentActivities($adherent);

        if ($token) {
            $this->manager->remove($token);
        }
        if ($summary) {
            $this->manager->remove($summary);
        }
        $this->manager->remove($adherent);
        $this->manager->flush();

        $this->mailer->sendMessage($message);
    }

    private function removeAdherentMemberShips(Adherent $adherent): void
    {
        $committeeRepository = $this->manager->getRepository(Committee::class);

        foreach ($adherent->getMemberships() as $membership) {
            $committee = $committeeRepository->findOneBy(['uuid' => $membership->getCommitteeUuid()->toString()]);
            $this->committeeManager->unfollowCommittee($adherent, $committee, false);
        }
    }
}
