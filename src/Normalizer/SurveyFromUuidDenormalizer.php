<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Jecoute\Survey;
use App\Repository\Jecoute\SurveyRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SurveyFromUuidDenormalizer implements DenormalizerInterface
{
    private $repository;

    public function __construct(SurveyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var Survey $survey */
        if ($survey = $this->repository->findOneByUuid($data)) {
            return $survey;
        }

        throw new ItemNotFoundException('Survey not found');
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return Survey::class === $type
            && \is_string($data)
            && Uuid::isValid($data);
    }
}
