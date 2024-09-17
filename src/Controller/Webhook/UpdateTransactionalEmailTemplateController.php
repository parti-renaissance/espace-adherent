<?php

namespace App\Controller\Webhook;

use App\Entity\Email\TransactionalEmailTemplate;
use App\Repository\Email\TransactionalEmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/templates', name: 'app_webhook_template_update', methods: ['POST'])]
class UpdateTransactionalEmailTemplateController extends AbstractController
{
    public function __construct(private readonly string $templateWebhookKey)
    {
    }

    public function __invoke(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, TransactionalEmailTemplateRepository $repository): Response
    {
        if ('Bearer '.$this->templateWebhookKey !== $request->headers->get('Authorization')) {
            return $this->json('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $serializer->deserialize($request->getContent(), TransactionalEmailTemplate::class, 'json', [
            'object_to_populate' => $template = new TransactionalEmailTemplate(),
        ]);

        if (null === $template->identifier) {
            return $this->json('Identifier is required', Response::HTTP_BAD_REQUEST);
        }

        if ($template->parent?->identifier) {
            $template->parent = $repository->findOneBy(['identifier' => $template->parent->identifier]);
        }

        /** @var TransactionalEmailTemplate $existingTemplate */
        if ($existingTemplate = $repository->findOneBy(['identifier' => $template->identifier])) {
            $existingTemplate->updateFrom($template);
        } else {
            $em->persist($template);
        }

        $em->flush();

        return $this->json('OK');
    }
}
