<?php

namespace App\Newsletter;

use App\Entity\NewsletterInvite;
use App\Mailer\MailerService;
use App\Mailer\Message\NewsletterInvitationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsletterInvitationHandler
{
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(ObjectManager $manager, MailerService $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(Invitation $invitation, ?string $ip)
    {
        foreach ($invitation->guests as $guest) {
            $invite = NewsletterInvite::create($invitation->firstName, $invitation->lastName, $guest, $ip);

            $this->manager->persist($invite);
            $this->mailer->sendMessage(NewsletterInvitationMessage::createFromInvite($invite, $this->urlGenerator->generate(
                'newsletter_subscription',
                ['mail' => $guest],
                UrlGeneratorInterface::ABSOLUTE_URL
            )));
        }

        $this->manager->flush();
    }
}
