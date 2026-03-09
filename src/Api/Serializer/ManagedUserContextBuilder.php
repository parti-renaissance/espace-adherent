<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Projection\ManagedUser;
use Symfony\Component\HttpFoundation\Request;

class ManagedUserContextBuilder implements SerializerContextBuilderInterface
{
    public const GROUP_VOX = 'managed_user_vox';
    public const GROUP_VOX_DETAIL = 'managed_user_vox_detail';

    public function __construct(private readonly SerializerContextBuilderInterface $decorated)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $resourceClass = $context['resource_class'] ?? null;
        if (ManagedUser::class !== $resourceClass) {
            return $context;
        }

        $apiContext = $context[PrivatePublicContextBuilder::CONTEXT_KEY] ?? null;
        if (PrivatePublicContextBuilder::CONTEXT_PUBLIC_CONNECTED_USER === $apiContext) {
            $context['groups'] = [self::GROUP_VOX, self::GROUP_VOX_DETAIL];
        }

        return $context;
    }
}
