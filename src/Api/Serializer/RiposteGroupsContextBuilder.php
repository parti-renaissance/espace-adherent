<?php

namespace App\Api\Serializer;

use App\Entity\Jecoute\Riposte;
use Symfony\Component\HttpFoundation\Request;

class RiposteGroupsContextBuilder extends AbstractGroupsContextBuilder
{
    protected function updateContext(array $context, Request $request): array
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
