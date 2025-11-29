<?php

declare(strict_types=1);

namespace App\Api\Validator;

use ApiPlatform\Symfony\Validator\ValidationGroupsGeneratorInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use Symfony\Component\HttpFoundation\RequestStack;

class UpdateDesignationGroupGenerator implements ValidationGroupsGeneratorInterface
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function __invoke($designation): array
    {
        \assert($designation instanceof Designation, new \RuntimeException('the object is not instance of Designation Entity'));

        $request = $this->requestStack->getMainRequest();

        /** @var Designation $oldDesignation */
        $oldDesignation = $request?->attributes->get('previous_data');

        if ($oldDesignation->isFullyEditable()) {
            return ['api_designation_write'];
        }

        return ['api_designation_write_limited'];
    }
}
