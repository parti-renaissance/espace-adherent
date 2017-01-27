<?php

namespace AppBundle\Membership;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\AdherentAccountActivationMessage;
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

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        UrlGeneratorInterface $urlGenerator,
        MailjetService $mailjet,
        ObjectManager $manager
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->urlGenerator = $urlGenerator;
        $this->mailjet = $mailjet;
        $this->manager = $manager;
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

        $membershipRequest->setAdherent($adherent);
    }

    public function update(Adherent $adherent, MembershipRequest $membershipRequest)
    {
        $adherent->updateMembership($membershipRequest, $this->addressFactory->createFromAddress($membershipRequest->getAddress()));

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
}
