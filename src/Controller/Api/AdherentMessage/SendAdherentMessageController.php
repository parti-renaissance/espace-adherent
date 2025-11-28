<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SendAdherentMessageController extends AbstractController
{
    public function __invoke(AdherentMessageManager $manager, AdherentMessage $message, #[CurrentUser] Adherent $adherent): Response
    {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to send.');
        }

        if (!$message->getSubject()) {
            throw new BadRequestHttpException('Subject is required.');
        }

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        if ($adherent->sandboxMode || $message->getAuthor()?->sandboxMode || $message->getSender()?->sandboxMode) {
            throw new \RuntimeException('An error occurred. Please try again later.');
        }

        $manager->send($message, $manager->getRecipients($message));

        return $this->json('OK');
    }
}
