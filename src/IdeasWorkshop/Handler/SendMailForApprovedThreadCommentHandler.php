<?php

namespace AppBundle\IdeasWorkshop\Handler;

use AppBundle\Entity\IdeasWorkshop\BaseComment;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\IdeasWorkshop\Command\SendMailForApprovedThreadCommentCommand;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\ApprovedIdeaCommentMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendMailForApprovedThreadCommentHandler implements MessageHandlerInterface
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(SendMailForApprovedThreadCommentCommand $command): void
    {
        $comment = $command->getComment();

        $this->mailer->sendMessage(ApprovedIdeaCommentMessage::create(
            $comment->getAuthor(),
            $this->getIdeaName($comment)
        ));
    }

    private function getIdeaName(BaseComment $comment): string
    {
        if ($comment instanceof Thread) {
            return $comment->getIdea()->getName();
        }

        if ($comment instanceof ThreadComment) {
            return $comment->getThread()->getIdea()->getName();
        }

        return '';
    }
}
