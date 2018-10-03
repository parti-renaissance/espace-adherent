<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterInvite;
use AppBundle\Mail\Transactional\NewsletterInvitationMail;
use AppBundle\Mail\Transactional\NewsletterSubscriptionMail;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\NewsletterInvitationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsletterInvitationHandler
{
    private $manager;
    private $mailPost;
    private $urlGenerator;

    public function __construct(ObjectManager $manager, MailPostInterface $mailPost, UrlGeneratorInterface $urlGenerator)
    {
        $this->manager = $manager;
        $this->mailPost = $mailPost;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(Invitation $invitation, ?string $ip)
    {
        foreach ($invitation->guests as $guest) {
            $invite = NewsletterInvite::create($invitation->firstName, $invitation->lastName, $guest, $ip);

            $this->manager->persist($invite);

            $inviteUrl = $this->urlGenerator->generate(
                'newsletter_subscription',
                ['mail' => $guest],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $this->mailPost->address(
                NewsletterInvitationMail::class,
                NewsletterInvitationMail::createRecipientFor($invite),
                null,
                NewsletterInvitationMail::createTemplateVarsFrom($invite, $inviteUrl),
                NewsletterInvitationMail::createSubjectFor($invite)
            );
        }

        $this->manager->flush();
    }
}
