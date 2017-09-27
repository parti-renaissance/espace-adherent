<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterInvite;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\NewsletterInvitationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsletterInvitationHandler
{
    private $manager;
    private $mailjet;
    private $urlGenerator;

    public function __construct(ObjectManager $manager, MailjetService $mailjet, UrlGeneratorInterface $urlGenerator)
    {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(Invitation $invitation, ?string $ip)
    {
        foreach ($invitation->guests as $guest) {
            $invite = NewsletterInvite::create($invitation->firstName, $invitation->lastName, $guest, $ip);

            $this->manager->persist($invite);
            $this->mailjet->sendMessage(NewsletterInvitationMessage::createFromInvite($invite, $this->urlGenerator->generate(
                'newsletter_subscription',
                ['mail' => $guest],
                UrlGeneratorInterface::ABSOLUTE_URL
            )));
        }

        $this->manager->flush();
    }
}
