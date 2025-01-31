<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'messages') and (subject.getAuthor() == user or user.hasDelegatedFromUser(subject.getAuthor(), 'messages'))"), subject: 'message')]
class SendAdherentMessageController extends AbstractController
{
    public function __construct(private readonly AdherentMessageManager $manager)
    {
    }

    public function __invoke(AbstractAdherentMessage $message): Response
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

        $this->manager->send($message, $this->manager->getRecipients($message));

        return $this->json('OK');
    }
}
