<?php

namespace App\IdeasWorkshop\Handler;

use App\IdeasWorkshop\Command\SendMailForPublishedIdeaCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\IdeaPublishMessage;
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
