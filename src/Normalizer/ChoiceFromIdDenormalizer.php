<?php

namespace App\Normalizer;

use ApiPlatform\Core\Exception\ItemNotFoundException;
use App\Entity\Jecoute\Choice;
use App\Repository\Jecoute\ChoiceRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ChoiceFromIdDenormalizer implements DenormalizerInterface
{
    private $repository;

    public function __construct(ChoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var Choice $choice */
        if ($choice = $this->repository->find($data)) {
            return $choice;
        }

        throw new ItemNotFoundException();
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return Choice::class === $type && \is_integer($data);
    }
}
