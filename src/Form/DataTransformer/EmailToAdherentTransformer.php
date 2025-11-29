<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EmailToAdherentTransformer implements DataTransformerInterface
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function transform($value): mixed
    {
        if ($value instanceof Adherent) {
            return $value->getEmailAddress();
        }

        return $value;
    }

    public function reverseTransform($value): mixed
    {
        if ($value) {
            if (!$adherent = $this->adherentRepository->findOneByEmail($value)) {
                throw new TransformationFailedException('Adhérent avec cet email n\'existe pas');
            }

            return $adherent;
        }

        return $value;
    }
}
