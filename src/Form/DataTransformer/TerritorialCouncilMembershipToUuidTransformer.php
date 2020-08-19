<?php

namespace App\Form\DataTransformer;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\DataTransformerInterface;

class TerritorialCouncilMembershipToUuidTransformer implements DataTransformerInterface
{
    private $repository;

    public function __construct(TerritorialCouncilMembershipRepository $repository)
    {
        $this->repository = $repository;
    }

    public function transform($value)
    {
        if ($value instanceof TerritorialCouncilMembership) {
            return $value->getUuid()->toString();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (Uuid::isValid($value) && $membership = $this->repository->findOneByUuid($value)) {
            return $membership;
        }

        return null;
    }
}
