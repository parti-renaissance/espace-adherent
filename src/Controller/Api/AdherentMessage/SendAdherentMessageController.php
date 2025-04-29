<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox']) and (subject.getAuthor() == user or user.hasDelegatedFromUser(subject.getAuthor(), 'messages') or user.hasDelegatedFromUser(subject.getAuthor(), 'messages_vox'))"), subject: 'message')]
class SendAdherentMessageController extends AbstractController
{
    public function __construct(private readonly AdherentMessageManager $manager)
    {
    }

    public function __invoke(AbstractAdherentMessage $message, #[CurrentUser] Adherent $adherent): Response
    {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to send.');
        }

        if (!$message->getRecipientCount()) {
            throw new BadRequestHttpException('Your message should have a filter');
        }

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        if ($adherent->sandboxMode || $message->getAuthor()?->sandboxMode) {
            throw new \RuntimeException('An error occurred. Please try again later.');
        }

        $this->manager->send($message, $this->manager->getRecipients($message));

        return $this->json('OK');
    }
}
