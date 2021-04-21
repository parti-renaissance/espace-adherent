<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/adherent_messages/{uuid}", name="app_api_get_adherent_message_status", methods={"GET"})
 *
 * @Security("is_granted('ROLE_ADHERENT') and (message.getAuthor() == user or user.hasDelegatedFromUser(message.getAuthor(), 'messages'))")
 */
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
