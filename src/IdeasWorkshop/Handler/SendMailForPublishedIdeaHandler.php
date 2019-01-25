<?php

namespace AppBundle\IdeasWorkshop\Handler;

use AppBundle\IdeasWorkshop\Command\SendMailForPublishedIdeaCommand;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\IdeaPublishMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendMailForPublishedIdeaHandler implements MessageHandlerInterface
{
    private $mailer;
    private $urlGenerator;

    public function __construct(MailerService $mailer, UrlGeneratorInterface $urlGenerator)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(SendMailForPublishedIdeaCommand $command): void
    {
        $idea = $command->getIdea();

        $this->mailer->sendMessage(IdeaPublishMessage::create(
            $idea->getAuthor(),
            $this->urlGenerator->generate(
                'react_app_ideas_workshop_proposition',
                ['id' => $idea->getUuidAsString()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ));
    }
}
