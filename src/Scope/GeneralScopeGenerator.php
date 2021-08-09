<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Scope\Exception\NotFoundScopeGeneratorException;
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

    public function getGenerator(string $scopeCode, Adherent $adherent): ScopeGeneratorInterface
    {
        foreach ($this->generators as $generator) {
            if ($generator->getCode() === $scopeCode) {
                if ($generator->supports($adherent)) {
                    return $generator;
                }

                break;
            }
        }

        throw new NotFoundScopeGeneratorException("Scope generator not found for '$scopeCode'");
    }
}
