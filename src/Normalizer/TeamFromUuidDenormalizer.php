<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Team\Team;
use App\Repository\Team\TeamRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TeamFromUuidDenormalizer implements DenormalizerInterface
{
    private TeamRepository $repository;

    public function __construct(TeamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var Team $team */
        if ($team = $this->repository->findOneByUuid($data)) {
            return $team;
        }

        throw new ItemNotFoundException();
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return Team::class === $type
            && \is_string($data)
            && Uuid::isValid($data);
    }
}
