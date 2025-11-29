<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AdherentToIdTransformer implements DataTransformerInterface
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function transform($adherent): mixed
    {
        return $adherent instanceof Adherent ? $adherent->getId() : null;
    }

    public function reverseTransform($adherentId): mixed
    {
        if (null === $adherentId) {
            return null;
        }

        $adherent = $this->adherentRepository->findOneActiveById($adherentId);

        if (!$adherent) {
            throw new TransformationFailedException(\sprintf('An Adherent with id "%d" does not exist.', $adherentId));
        }

        return $adherent;
    }
}
