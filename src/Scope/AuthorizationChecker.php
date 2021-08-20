<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Scope\Exception\InvalidFeatureCodeException;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Exception\NotFoundScopeGeneratorException;
use App\Scope\Exception\ScopeQueryParamMissingException;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationChecker
{
    private const SCOPE_QUERY_PARAM = 'scope';
    private const SCOPE_POSITION_PARAM = 'scope_position';

    private const SCOPE_POSITION_REQUEST = 'request';
    private const SCOPE_POSITION_QUERY = 'query';

    private $scopeGenerator;

    public function __construct(GeneralScopeGenerator $scopeGenerator)
    {
        $this->scopeGenerator = $scopeGenerator;
    }

    public function isFeatureGranted(Request $request, Adherent $adherent, string $featureCode): bool
    {
        if (!$scope = $this->getScope($request)) {
            throw new ScopeQueryParamMissingException();
        }

        if (!ScopeEnum::isValid($scope)) {
            throw new InvalidScopeException();
        }

        if (!FeatureEnum::isValid($featureCode)) {
            throw new InvalidFeatureCodeException();
        }

        $scope = $this->scopeGenerator->getGenerator($scope, $adherent)->generate($adherent);

        return $scope->hasFeature($featureCode);
    }

    public function isScopeGranted(string $scope, Adherent $adherent): bool
    {
        try {
            return (bool) $this->scopeGenerator->getGenerator($scope, $adherent);
        } catch (NotFoundScopeGeneratorException $e) {
            return false;
        }
    }

    public function getScope(Request $request): ?string
    {
        $scopePosition = $request->attributes->get(self::SCOPE_POSITION_PARAM, self::SCOPE_POSITION_QUERY);

        if (self::SCOPE_POSITION_REQUEST === $scopePosition) {
            $content = json_decode($request->getContent(), true);

            return $content[self::SCOPE_QUERY_PARAM] ?? null;
        }

        return $request->query->get(self::SCOPE_QUERY_PARAM);
    }
}
