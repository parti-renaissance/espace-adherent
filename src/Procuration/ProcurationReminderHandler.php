<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mail\Transactional\ProcurationProxyReminderMail;
use AppBundle\Routing\RemoteUrlGenerator;
use EnMarche\MailerBundle\Mail\Recipient;
use EnMarche\MailerBundle\Mail\RecipientInterface;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class ProcurationReminderHandler
{
    private $mailPost;
    private $urlGenerator;
    private $replyToEmailAddress;

    public function __construct(
        MailPostInterface $mailPost,
        RemoteUrlGenerator $urlGenerator,
        string $replyToEmailAddress
    ) {
        $this->mailPost = $mailPost;
        $this->urlGenerator = $urlGenerator;
        $this->replyToEmailAddress = $replyToEmailAddress;
    }

    /**
     * @param ProcurationRequest[] $requests
     */
    public function remind(array $requests): void
    {
        if (empty($requests)) {
            return;
        }

        /** @var RecipientInterface[] $recipients */
        $recipients = array_map(function (ProcurationRequest $request) {
            return ProcurationProxyReminderMail::createRecipient(
                $request,
                $this->urlGenerator->generateRemoteUrl('app_procuration_my_request', [
                    'id' => $request->getId(),
                    'token' => $request->generatePrivateToken(),
                ])
            );
        }, $requests);

        if ($proxy = $requests[0]->getFoundProxy()) {
            $recipients[] = new Recipient($proxy->getEmailAddress());
        }

        $this->mailPost->address(
            ProcurationProxyReminderMail::class,
            $recipients,
            ProcurationProxyReminderMail::createReplyToFromEmail($this->replyToEmailAddress),
            $recipients[0]->getTemplateVars(),
            ProcurationProxyReminderMail::SUBJECT,
            ProcurationProxyReminderMail::createSender()
        );

        foreach ($requests as $request) {
            $request->remind();
        }
    }
}
