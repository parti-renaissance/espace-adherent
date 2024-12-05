<?php

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Jecoute\News;
use App\Scope\AuthorizationChecker;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use App\Security\Voter\RequestScopeVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class JecouteNewsGroupsContextBuilder implements SerializerContextBuilderInterface
{
    private SerializerContextBuilderInterface $decorated;
    private AuthorizationCheckerInterface $authorizationChecker;
    private AuthorizationChecker $scopeAuthorizationChecker;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        AuthorizationCheckerInterface $authorizationChecker,
        AuthorizationChecker $scopeAuthorizationChecker,
    ) {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
        $this->scopeAuthorizationChecker = $scopeAuthorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (News::class !== $resourceClass
            || !$this->authorizationChecker->isGranted(RequestScopeVoter::SCOPE_AND_FEATURE_GRANTED, FeatureEnum::NEWS)
            || !\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
        ) {
            return $context;
        }

        $scope = $this->scopeAuthorizationChecker->getScope($request);
        if (ScopeEnum::NATIONAL === $scope) {
            $context['groups'][] = 'jecoute_news_write_national';
        }

        return $context;
    }
}
