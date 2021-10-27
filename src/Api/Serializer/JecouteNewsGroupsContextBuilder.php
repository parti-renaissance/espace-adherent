<?php

namespace App\Api\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\Jecoute\News;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class JecouteNewsGroupsContextBuilder extends AbstractGroupsContextBuilder
{
    private AuthorizationChecker $scopeAuthorizationChecker;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        AuthorizationCheckerInterface $authorizationChecker,
        AuthorizationChecker $scopeAuthorizationChecker
    ) {
        parent::__construct($decorated, $authorizationChecker);

        $this->scopeAuthorizationChecker = $scopeAuthorizationChecker;
    }

    protected function updateContext(array $context, Request $request): array
    {
        $resourceClass = $context['resource_class'] ?? null;
        if (News::class !== $resourceClass
            || !$this->authorizationChecker->isGranted('IS_FEATURE_GRANTED', 'news')) {
            return $context;
        }

        $context['groups'][] = 'jecoute_news_read_dc';

        $scope = $this->scopeAuthorizationChecker->getScope($request);
        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])
            && ScopeEnum::NATIONAL === $scope) {
            $context['groups'][] = 'jecoute_news_write_national';
        }

        return $context;
    }
}
