<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Generator\ScopeGeneratorInterface;

class GeneralScopeGenerator
{
    /**
     * @var ScopeGeneratorInterface[]|iterable
     */
    private $generators;

    public function __construct(iterable $generators)
    {
        $this->generators = $generators;
    }

    /**
     * @return Scope[]
     */
    public function generateScopes(Adherent $adherent): array
    {
        $scopes = [];

        /** @var ScopeGeneratorInterface $generator */
        foreach ($this->generators as $generator) {
            if ($generator->supports($adherent)) {
                $scopes[] = $generator->generate($adherent);
            }
        }

        return $scopes;
    }

    public function generate(Adherent $adherent, string $scope): ?Scope
    {
        if (!\in_array($scope, ScopeEnum::toArray())) {
            throw new InvalidScopeException(sprintf('Invalid scope "%s"', $scope));
        }

        foreach ($this->generators as $generator) {
            if ($generator->supportsScope($scope, $adherent)) {
                return $generator->generate($adherent);
            }
        }

        return null;
    }
}
