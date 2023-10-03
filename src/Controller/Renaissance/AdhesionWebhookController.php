<?php

namespace App\Controller\Renaissance;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Repository\Renaissance\AdherentRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/adhesion/webhook/{key}', name: 'app_adhesion_webhook', methods: ['POST'])]
class AdhesionWebhookController extends AbstractController
{
    public const WEBHOOK_SOURCE = 'webhook';

    public function __construct(
        private readonly string $renaissanceWebhookKey,
        private readonly AdherentRequestRepository $adherentRequestRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(
        string $key,
        Request $request): Response
    {
        if ($key === $this->renaissanceWebhookKey && $request->isMethod(Request::METHOD_POST)) {
            $email = $request->toArray()['email'] ?? null;
            if ($email && 0 === $this->validator->validate($email, [new Email()])->count()) {
                if (!$adherentRequest = $this->adherentRequestRepository->findOneForEmail($email, self::WEBHOOK_SOURCE)) {
                    $this->entityManager->persist(AdherentRequest::createForEmail($email, self::WEBHOOK_SOURCE));
                } else {
                    $adherentRequest->setUpdatedAt(new \DateTime());
                }

                $this->entityManager->flush();
            }
        }

        return new Response('OK');
    }
}
