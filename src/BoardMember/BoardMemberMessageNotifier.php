<?php

namespace AppBundle\BoardMember;

use AppBundle\Mail\Transactional\BoardMemberMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class BoardMemberMessageNotifier
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function sendMessage(BoardMemberMessage $message): void
    {
        $this->mailPost->address(
            BoardMemberMail::class,
            BoardMemberMail::createRecipients($message),
            BoardMemberMail::createReplyTo($message),
            BoardMemberMail::createTemplateVars($message),
            $message->getSubject(),
            BoardMemberMail::createSender($message)
        );
    }
}
