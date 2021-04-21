<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Security("is_granted('ROLE_MESSAGE_REDACTOR') and (message.getAuthor() == user or user.hasDelegatedFromUser(message.getAuthor(), 'messages'))")
 */
class SendAdherentMessageController extends AbstractController
{
    private $manager;

    public function __construct(AdherentMessageManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(AbstractAdherentMessage $message)
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

        $this->manager->send($message);

        return $this->json('OK');
    }
}
