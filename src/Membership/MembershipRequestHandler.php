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
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\AdherentAccountActivationMessage;
use AppBundle\Mailjet\Message\AdherentTerminateMembershipMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $addressFactory;
    private $urlGenerator;
    private $mailjet;
    private $manager;
    private $committeeManager;
    private $registrationManager;
    private $citizenInitiativeManager;
    private $committeeFeedManager;
    private $activitySubscriptionManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        UrlGeneratorInterface $urlGenerator,
        MailjetService $mailjet,
        ObjectManager $manager,
        CommitteeManager $committeeManager,
        EventRegistrationManager $registrationManager,
        CitizenInitiativeManager $citizenInitiativeManager,
        CommitteeFeedManager $committeeFeedManager,
        ActivitySubscriptionManager $activitySubscriptionManager
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->urlGenerator = $urlGenerator;
        $this->mailjet = $mailjet;
        $this->manager = $manager;
        $this->committeeManager = $committeeManager;
        $this->registrationManager = $registrationManager;
        $this->citizenInitiativeManager = $citizenInitiativeManager;
        $this->committeeFeedManager = $committeeFeedManager;
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
        $this->mailjet->sendMessage(AdherentAccountActivationMessage::createFromAdherent($adherent, $activationUrl));

        $this->dispatcher->dispatch(AdherentEvents::REGISTRATION_COMPLETED, new AdherentAccountWasCreatedEvent($adherent));
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

        $this->removeAdherentMemberShips($adherent);
        $this->citizenInitiativeManager->removeOrganizerCitizenInitiatives($adherent);
        $this->registrationManager->anonymizeAdherentRegistrations($adherent);
        $this->committeeFeedManager->removeAuthorItems($adherent);
        $this->activitySubscriptionManager->removeAdherentActivities($adherent);

        if ($token) {
            $this->manager->remove($token);
        }
        $this->manager->remove($adherent);
        $this->manager->flush();

        $this->mailjet->sendMessage($message);
    }

    private function removeAdherentMemberShips(Adherent $adherent): void
    {
        foreach ($adherent->getMemberships() as $membership) {
            $committee = $this->manager->getRepository(Committee::class)->findOneBy(['uuid' => $membership->getCommitteeUuid()->toString()]);
            $this->committeeManager->unfollowCommittee($adherent, $committee, false);
        }
    }
}
