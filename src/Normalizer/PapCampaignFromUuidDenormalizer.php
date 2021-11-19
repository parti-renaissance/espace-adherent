<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Pap\Campaign;
use App\Repository\Pap\CampaignRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PapCampaignFromUuidDenormalizer implements DenormalizerInterface
{
    private $repository;

    public function __construct(CampaignRepository $repository)
    {
        $this->repository = $repository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var Campaign $campaign */
        if ($campaign = $this->repository->findOneByUuid($data)) {
            return $campaign;
        }

        throw new ItemNotFoundException();
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return Campaign::class === $type
            && \is_string($data)
            && Uuid::isValid($data);
    }
}
