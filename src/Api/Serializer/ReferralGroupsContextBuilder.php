<?php

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Referral;
use Symfony\Component\HttpFoundation\Request;

class ReferralGroupsContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(private readonly SerializerContextBuilderInterface $decorated)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (Referral::class === $resourceClass && PrivatePublicContextBuilder::CONTEXT_PRIVATE === $context[PrivatePublicContextBuilder::CONTEXT_KEY]) {
            $context['groups'][] = 'referral_read_with_referrer';
        }

        return $context;
    }
}
