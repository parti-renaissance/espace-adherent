<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Security("is_granted('ROLE_MESSAGE_REDACTOR') and (message.getAuthor() == user or user.hasDelegatedFromUser(message.getAuthor(), 'messages'))")
 */
class SendTestAdherentMessageController extends AbstractController
{
    private $manager;

    public function __construct(AdherentMessageManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(UserInterface $user, AbstractAdherentMessage $message)
    {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to send.');
        }

        if ($this->manager->sendTest($message, $user)) {
            return $this->json('OK');
        }

        return $this->json('Une erreur inconnue est survenue', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
