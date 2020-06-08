<?php

namespace App\Form\DataTransformer;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AdherentToEmailTransformer implements DataTransformerInterface
{
    /** @var AdherentRepository */
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function transform($value)
    {
        return $value instanceof Adherent ? $value->getEmailAddress() : null;
    }

    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        $adherent = $this->adherentRepository->findOneByEmail($value);

        if (null === $adherent) {
            throw new TransformationFailedException(sprintf('No adherent found with email "%s".', $value));
        }

        return $adherent;
    }
}
