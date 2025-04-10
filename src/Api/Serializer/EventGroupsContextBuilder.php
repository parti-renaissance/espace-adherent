<?php

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Event\Event;
use Symfony\Component\HttpFoundation\Request;

class EventGroupsContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(private SerializerContextBuilderInterface $decorated)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (Event::class !== $resourceClass
            || Request::METHOD_PUT !== $request->getMethod()
            || $normalization
        ) {
            return $context;
        }

        /** @var Event $event */
        $event = $request->attributes->get('data');

        if ($event->getBeginAt() < new \DateTime()) {
            array_splice($context['groups'], array_search('event_write', $context['groups'], true), 1);
            $context['groups'][] = 'event_write_limited';
        }

        return $context;
    }
}
