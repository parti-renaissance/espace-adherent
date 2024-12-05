<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted(new Expression("is_granted('ROLE_USER') and (subject.getAuthor() == user or user.hasDelegatedFromUser(subject.getAuthor(), 'messages'))"), subject: 'message')]
#[Route(path: '/adherent_messages/{uuid}', name: 'app_api_get_adherent_message_status', methods: ['GET'])]
class GetAdherentMessageStatusController extends AbstractController
{
    public function __invoke(AbstractAdherentMessage $message, SerializerInterface $serializer): Response
    {
        return $this->json(
            $message,
            Response::HTTP_OK,
            [],
            ['groups' => ['message_read']]
        );
    }
}
