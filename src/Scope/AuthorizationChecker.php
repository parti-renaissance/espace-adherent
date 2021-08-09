<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Scope\Exception\InvalidFeatureCodeException;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Exception\ScopeQueryParamMissingException;
use Symfony\Component\HttpFoundation\Request;

class AuthorizationChecker
{
    private const SCOPE_QUERY_PARAM = 'scope';

    private $scopeGenerator;

    public function __construct(GeneralScopeGenerator $scopeGenerator)
    {
        $this->scopeGenerator = $scopeGenerator;
    }

    public function isFeatureGranted(Request $request, Adherent $adherent, string $featureCode): bool
    {
        if (!$scope = $request->query->get(self::SCOPE_QUERY_PARAM)) {
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
}
