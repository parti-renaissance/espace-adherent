<?php

namespace App\Api\Validator;

use ApiPlatform\Symfony\Validator\ValidationGroupsGeneratorInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Scope\FeatureEnum;
use App\Security\Voter\FeatureVoter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdateDesignationGroupGenerator implements ValidationGroupsGeneratorInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function __invoke($designation): array
    {
        \assert($designation instanceof Designation);

        $request = $this->requestStack->getMainRequest();

        /** @var Designation $oldDesignation */
        $oldDesignation = $request?->attributes->get('previous_data');

        if ($this->authorizationChecker->isGranted(FeatureVoter::PERMISSION, FeatureEnum::DESIGNATION)
            && 'api_designations_put_item' === $request?->attributes->get('_api_item_operation_name')
            && $oldDesignation->isVotePeriodStarted()
        ) {
            return ['api_designation_write_limited'];
        }

        return ['api_designation_write'];
    }
}
