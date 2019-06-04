<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\Committee;
use AppBundle\Repository\CommitteeRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CommitteeToIdTransformer implements DataTransformerInterface
{
    private $committeeRepository;

    public function __construct(CommitteeRepository $committeeRepository)
    {
        $this->committeeRepository = $committeeRepository;
    }

    public function transform($committee)
    {
        return $committee instanceof Committee ? $committee->getId() : null;
    }

    public function reverseTransform($committeeId)
    {
        $committee = $this->committeeRepository->findOneBy(['id' => $committeeId]);

        if (!$committee) {
            throw new TransformationFailedException(sprintf(
                'A Committee with id "%d" does not exist.',
                $committeeId
            ));
        }

        return $committee;
    }
}
