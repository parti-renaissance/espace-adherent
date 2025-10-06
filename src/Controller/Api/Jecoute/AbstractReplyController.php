<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractReplyController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ValidatorInterface $validator,
        protected readonly SerializerInterface $serializer,
        ?LoggerInterface $logger = null,
    ) {
        if ($logger) {
            $this->setLogger($logger);
        }
    }

    public function handleRequest(Request $request, ?DataSurveyAwareInterface $object = null): Response
    {
        if ($object?->getDataSurvey()) {
            return $this->json([
                'code' => 'already_replied',
                'message' => 'La réponse a été déjà envoyée',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->serializer->deserialize($request->getContent(), DataSurvey::class, 'json', [
            AbstractObjectNormalizer::GROUPS => array_merge(['data_survey_write'], $this->getCustomDeserializeGroups()),
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $dataSurvey = $this->initializeDataSurvey($request, $object),
        ]);

        $errors = $this->validator->validate($dataSurvey);

        if ($errors->count()) {
            if ($this->logger) {
                $this->logger->error('PAP replay: Bad request', ['errors' => $errors]);
            }

            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($dataSurvey);

        $object?->setDataSurvey($dataSurvey);
        $this->postHandleAction();

        $this->entityManager->flush();

        $this->dispatch();

        return $this->json($dataSurvey, Response::HTTP_CREATED, [], ['groups' => ['data_survey_read']]);
    }

    protected function postHandleAction(): void
    {
    }

    protected function dispatch(): void
    {
    }

    protected function initializeDataSurvey(Request $request, ?DataSurveyAwareInterface $object = null): DataSurvey
    {
        return new DataSurvey();
    }

    protected function getCustomDeserializeGroups(): array
    {
        return [];
    }
}
