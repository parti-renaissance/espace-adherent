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
            BoardMemberMail::createRecipientsFrom($message),
            BoardMemberMail::createReplyToFrom($message),
            BoardMemberMail::createTemplateVarsFrom($message),
            $message->getSubject(),
            BoardMemberMail::createSenderFrom($message)
        );
    }
}
