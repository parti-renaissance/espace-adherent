<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Jecoute\JemarcheDataSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Phoning\CampaignHistory;
use App\Exception\InvalidUuidException;
use App\Jecoute\Exception\DataSurveyException;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReplyController extends AbstractController
{
    private $entityManager;
    private $validator;
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function __invoke(Survey $survey, Request $request): Response
    {
        $this->serializer->deserialize($requestContent = $request->getContent(), DataSurvey::class, 'json', [
            AbstractObjectNormalizer::GROUPS => ['data_survey_write'],
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $dataSurvey = new DataSurvey($survey),
        ]);

        $errors = $this->validator->validate($dataSurvey);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($dataSurvey);

        try {
            $this->updateDataSurveyLink($dataSurvey, $requestContent);
        } catch (InvalidUuidException|DataSurveyException $e) {
            return $this->json(['message' => $e->getMessage(), 'path' => $e->getPath()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        return $this->json($dataSurvey, Response::HTTP_CREATED, [], ['groups' => 'data_survey_read']);
    }

    private function updateDataSurveyLink(DataSurvey $dataSurvey, string $requestContent): void
    {
        $requestData = json_decode($requestContent, true);
        $repository = $uuid = null;

        if (!empty($requestData[$path = 'phoning_campaign_history_uuid'])) {
            if (!Uuid::isValid($uuid = $requestData[$path])) {
                throw new InvalidUuidException('UUID invalid', 0, null, $path);
            }

            $repository = $this->entityManager->getRepository(CampaignHistory::class);
        } elseif (!empty($requestData[$path = 'jemarche_history_uuid'])) {
            if (!Uuid::isValid($uuid = $requestData[$path])) {
                throw new InvalidUuidException('UUID invalid', 0, null, $path);
            }

            $repository = $this->entityManager->getRepository(JemarcheDataSurvey::class);
        }

        if ($uuid && $repository) {
            if (!($object = $repository->findOneBy(['uuid' => $uuid])) instanceof DataSurveyAwareInterface) {
                throw DataSurveyException::objectNotFound($path);
            }

            if ($object->getDataSurvey()) {
                throw DataSurveyException::objectInvalid($path);
            }

            $object->setDataSurvey($dataSurvey);
        }
    }
}
