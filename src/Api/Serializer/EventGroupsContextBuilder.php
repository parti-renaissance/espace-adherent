<?php

namespace App\Api\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\Event\BaseEvent;
use App\Scope\FeatureEnum;
use App\Security\Voter\FeatureVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventGroupsContextBuilder implements SerializerContextBuilderInterface
{
    private SerializerContextBuilderInterface $decorated;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (BaseEvent::class !== $resourceClass
            || !$this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN')
            || !$this->authorizationChecker->isGranted(FeatureVoter::PERMISSION, FeatureEnum::EVENTS)
        ) {
            return $context;
        }

        if ('api_base_events_get_collection' === $request->get('_route')) {
            $context['groups'][] = 'event_list_read_extended';
        } elseif ('api_base_events_get_item' === $request->get('_route')) {
            $context['groups'][] = 'event_read_extended';
        }

        return $context;
    }
}
