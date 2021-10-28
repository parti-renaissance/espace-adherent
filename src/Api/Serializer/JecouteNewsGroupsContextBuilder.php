<?php

namespace App\Api\Serializer;

use App\Entity\Jecoute\News;

class JecouteNewsGroupsContextBuilder extends AbstractGroupsContextBuilder
{
    protected function updateContext(array $context): array
    {
        $resourceClass = $context['resource_class'] ?? null;
        if (News::class === $resourceClass
            && $this->authorizationChecker->isGranted('IS_FEATURE_GRANTED', 'news')
        ) {
            $context['groups'][] = 'jecoute_news_read_dc';
        }

        return $context;
    }
}
