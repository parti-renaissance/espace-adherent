<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Coalition\Cause;
use App\Repository\Coalition\CauseRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CauseFromUuidDenormalizer implements DenormalizerInterface
{
    private $repository;

    public function __construct(CauseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var Cause $cause */
        if ($cause = $this->repository->findOneByUuid($data)) {
            return $cause;
        }

        throw new ItemNotFoundException();
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return Cause::class === $type
            && \is_string($data)
            && Uuid::isValid($data);
    }
}
