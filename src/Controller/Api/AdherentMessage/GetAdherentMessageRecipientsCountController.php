<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_USER') and (subject.getAuthor() == user or user.hasDelegatedFromUser(subject.getAuthor(), 'messages') or user.hasDelegatedFromUser(subject.getAuthor(), 'messages_vox'))"), subject: 'message')]
#[Route(path: '/adherent_messages/{uuid}/count-recipients', name: 'app_api_get_adherent_message_count_recipients', methods: ['GET'])]
class GetAdherentMessageRecipientsCountController extends AbstractController
{
    public function __invoke(AbstractAdherentMessage $message, AdherentRepository $adherentRepository): Response
    {
        return $this->json(
            [
                'push' => $adherentRepository->countAdherentsForMessage($message, false, true),
                'email' => $message->getRecipientCount() ?: $adherentRepository->countAdherentsForMessage($message, true, false),
                'push_email' => $adherentRepository->countAdherentsForMessage($message, true, true),
                'in_app' => $adherentRepository->countAdherentsForMessage($message, null, null, true),
            ],
        );
    }
}
