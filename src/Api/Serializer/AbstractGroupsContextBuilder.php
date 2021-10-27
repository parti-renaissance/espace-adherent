<?php

namespace App\Api\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractGroupsContextBuilder implements SerializerContextBuilderInterface
{
    protected SerializerContextBuilderInterface $decorated;
    protected AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        return $this->updateContext($this->decorated->createFromRequest($request, $normalization, $extractedAttributes), $request);
    }

    abstract protected function updateContext(array $context, Request $request): array;
}
