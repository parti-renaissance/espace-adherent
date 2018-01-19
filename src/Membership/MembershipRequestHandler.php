<?php

namespace AppBundle\Membership;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentAccountActivationMessage;
use AppBundle\Mailer\Message\AdherentTerminateMembershipMessage;
use AppBundle\OAuth\CallbackManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $addressFactory;
    private $callbackManager;
    private $mailer;
    private $manager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        CallbackManager $callbackManager,
        MailerService $mailer,
        ObjectManager $manager,
        AdherentRegistry $adherentRegistry
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->callbackManager = $callbackManager;
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->adherentRegistry = $adherentRegistry;
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

        return $this->callbackManager->generateUrl('app_membership_activate', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function terminateMembership(UnregistrationCommand $command, Adherent $adherent)
    {
        $unregistrationFactory = new UnregistrationFactory();
        $unregistration = $unregistrationFactory->createFromUnregistrationCommandAndAdherent($command, $adherent);

        $this->adherentRegistry->unregister($adherent, $unregistration);

        $message = AdherentTerminateMembershipMessage::createFromAdherent($adherent);
        $this->mailer->sendMessage($message);
    }
}
