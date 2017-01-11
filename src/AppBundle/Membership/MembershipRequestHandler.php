<?php

namespace AppBundle\Membership;

use AppBundle\Entity\ActivationKey;
use AppBundle\Entity\Adherent;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\AdherentAccountActivationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MembershipRequestHandler
{
    private $adherentFactory;
    private $urlGenerator;
    private $mailjet;
    private $manager;

    public function __construct(
        AdherentFactory $adherentFactory,
        UrlGeneratorInterface $urlGenerator,
        MailjetService $mailjet,
        ObjectManager $manager
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->urlGenerator = $urlGenerator;
        $this->mailjet = $mailjet;
        $this->manager = $manager;
    }

    public function handle(MembershipRequest $membershipRequest)
    {
        $adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest);
        $activationKey = ActivationKey::generate(clone $adherent->getUuid());

        $this->manager->persist($adherent);
        $this->manager->persist($activationKey);
        $this->manager->flush();

        $activationUrl = $this->generateMembershipActivationUrl($adherent, $activationKey);
        $this->mailjet->sendMessage(AdherentAccountActivationMessage::createFromAdherent($adherent, $activationUrl));

        $membershipRequest->setAdherent($adherent);
    }

    private function generateMembershipActivationUrl(Adherent $adherent, ActivationKey $activationKey)
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'activation_key' => (string) $activationKey->getToken(),
        ];

        return $this->urlGenerator->generate('app_membership_activate', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
