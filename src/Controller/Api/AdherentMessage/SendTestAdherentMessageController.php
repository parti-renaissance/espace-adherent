<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'messages') and (message.getAuthor() == user or user.hasDelegatedFromUser(message.getAuthor(), 'messages'))"))]
class SendTestAdherentMessageController extends AbstractController
{
    public function __construct(private readonly AdherentMessageManager $manager)
    {
    }

    public function __invoke(AbstractAdherentMessage $message): Response
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        if ($this->manager->sendTest($message, $user)) {
            return $this->json('OK');
        }

        return $this->json('Une erreur inconnue est survenue', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
