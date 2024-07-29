<?php

namespace App\Form\DataTransformer;

use App\Entity\Donator;
use App\Repository\DonatorRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DonatorToIdentifierTransformer implements DataTransformerInterface
{
    private $donatorRepository;

    public function __construct(DonatorRepository $donatorRepository)
    {
        $this->donatorRepository = $donatorRepository;
    }

    public function transform($donator): mixed
    {
        return $donator instanceof Donator ? $donator->getIdentifier() : null;
    }

    public function reverseTransform($donatorIdentifier): mixed
    {
        $donator = $this->donatorRepository->findOneBy(['identifier' => $donatorIdentifier]);

        if (!$donator) {
            throw new TransformationFailedException(\sprintf('A Donator with identifier "%d" does not exist.', $donatorIdentifier));
        }

        return $donator;
    }
}
