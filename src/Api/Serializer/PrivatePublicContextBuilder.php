<?php

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\OAuth\Model\Scope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PrivatePublicContextBuilder implements SerializerContextBuilderInterface
{
    public const CONTEXT_KEY = 'api_context';

    public const CONTEXT_PRIVATE = 'private';
    public const CONTEXT_PUBLIC_ANONYMOUS = 'public:anonymous_user';
    public const CONTEXT_PUBLIC_CONNECTED_USER = 'public:connected_user';

    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $context[self::CONTEXT_KEY] = self::CONTEXT_PUBLIC_ANONYMOUS;

        if ($this->authorizationChecker->isGranted(Scope::generateRole(Scope::JEMENGAGE_ADMIN))) {
            $context[self::CONTEXT_KEY] = self::CONTEXT_PRIVATE;
        } elseif ($this->authorizationChecker->isGranted(Scope::generateRole(Scope::JEMARCHE_APP))) {
            $context[self::CONTEXT_KEY] = self::CONTEXT_PUBLIC_CONNECTED_USER;
        }

        return $context;
    }
}
