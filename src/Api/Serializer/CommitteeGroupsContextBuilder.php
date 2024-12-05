<?php

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Committee;
use App\Entity\CommitteeElection;
use App\VotingPlatform\Designation\DesignationStatusEnum;
use Symfony\Component\HttpFoundation\Request;

class CommitteeGroupsContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(private readonly SerializerContextBuilderInterface $decorated)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (Committee::class !== $resourceClass
            || Request::METHOD_PUT !== $request->getMethod()
            || $normalization
        ) {
            return $context;
        }

        /** @var Committee $committee */
        $committee = $request->attributes->get('data');

        $committeeElection = $committee->getCurrentElection();

        if ($committeeElection instanceof CommitteeElection
            && \in_array($committeeElection->getStatus(), [
                DesignationStatusEnum::SCHEDULED,
                DesignationStatusEnum::IN_PROGRESS,
            ])
        ) {
            array_splice($context['groups'], array_search('committee:write', $context['groups'], true), 1);
            $context['groups'][] = 'committee:write_limited';
        }

        return $context;
    }
}
