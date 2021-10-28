<?php

namespace App\Api\Serializer;

use App\Entity\Jecoute\Riposte;

class RiposteGroupsContextBuilder extends AbstractGroupsContextBuilder
{
    protected function updateContext(array $context): array
    {
        $resourceClass = $context['resource_class'] ?? null;
        if (Riposte::class === $resourceClass
            && !$this->authorizationChecker->isGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')
        ) {
            $context['groups'][] = 'riposte_read_dc';
        }

        return $context;
    }
}
