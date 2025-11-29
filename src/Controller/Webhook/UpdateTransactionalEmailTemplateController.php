<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Entity\Email\TransactionalEmailTemplate;
use App\Mailer\Command\UpdateTransactionalEmailTemplateCommand;
use App\Repository\Email\TransactionalEmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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

        $serializer->deserialize($request->getContent(), UpdateTransactionalEmailTemplateCommand::class, 'json', [
            'object_to_populate' => $command = new UpdateTransactionalEmailTemplateCommand(),
        ]);

        if (null === $command->identifier) {
            return $this->json('Identifier is required', Response::HTTP_BAD_REQUEST);
        }

        if ($command->parent) {
            if (!$parent = $repository->findOneBy(['identifier' => $command->parent])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Template parent n\'existe pas',
                ], Response::HTTP_BAD_REQUEST);
            }
            $command->parentObject = $parent;
        }

        /** @var TransactionalEmailTemplate $existingTemplate */
        if (!$template = $repository->findOneBy(['identifier' => $command->identifier])) {
            $em->persist($template = new TransactionalEmailTemplate());
        }

        $template->updateFrom($command);

        $em->flush();

        return $this->json('OK');
    }
}
