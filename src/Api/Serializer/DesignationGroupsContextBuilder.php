<?php

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\VotingPlatform\Designation\Designation;
use Symfony\Component\HttpFoundation\Request;

class DesignationGroupsContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(private SerializerContextBuilderInterface $decorated)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (Designation::class !== $resourceClass
            || Request::METHOD_PUT !== $request->getMethod()
            || $normalization
        ) {
            return $context;
        }

        /** @var Designation $designation */
        $designation = $request->attributes->get('data');

        if ($designation->getVoteEndDate() < new \DateTime()) {
            $context['groups'] = [];
        } elseif (!$designation->isFullyEditable() || ($designation->electionCreationDate && $designation->electionCreationDate < new \DateTime())) {
            array_splice($context['groups'], array_search('designation_write', $context['groups'], true), 1);
            $context['groups'][] = 'designation_write_limited';
        }

        return $context;
    }
}
