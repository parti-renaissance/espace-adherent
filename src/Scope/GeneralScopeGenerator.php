<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Scope\Exception\NotFoundScopeGeneratorException;
use App\Scope\Generator\ScopeGeneratorInterface;

class GeneralScopeGenerator
{
    /**
     * @var ScopeGeneratorInterface[]|iterable
     */
    private $generators;

    private $delegatedAccessRepository;

    public function __construct(iterable $generators, DelegatedAccessRepository $delegatedAccessRepository)
    {
        $this->generators = $generators;
        $this->delegatedAccessRepository = $delegatedAccessRepository;
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

        foreach ($adherent->getReceivedDelegatedAccesses() as $delegatedAccess) {
            $delegator = $delegatedAccess->getDelegator();

            try {
                $generator = $this->getGenerator($delegatedAccess->getType(), $delegator);
            } catch (NotFoundScopeGeneratorException $exception) { // Some delegated access types have no ScopeGenerator
                continue;
            }

            $generator->setDelegatedAccess($delegatedAccess);

            $scopes[] = $generator->generate($delegator);
        }

        return $scopes;
    }

    public function getGenerator(string $scopeCode, Adherent $adherent): ScopeGeneratorInterface
    {
        $isDelegatedScopeCode = $this->isDelegatedScopeCode($scopeCode);

        if ($isDelegatedScopeCode) {
            $delegatedAccess = $this->findDelegatedAccess($scopeCode);

            if (!$delegatedAccess) {
                throw new NotFoundScopeGeneratorException("Can't find delegated access for code \"$scopeCode\".");
            }

            $delegator = $delegatedAccess->getDelegator();

            foreach ($this->generators as $generator) {
                if (
                    $generator->getCode() === $delegatedAccess->getType()
                    && $generator->supports($delegator)
                ) {
                    $generator->setDelegatedAccess($delegatedAccess);

                    return $generator;
                }
            }

            throw new NotFoundScopeGeneratorException(sprintf('Scope generator not found for delegated access of type "%s" with uuid "%s".', $delegatedAccess->getType(), $delegatedAccess->getUuid()->toString()));
        }

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

    private function isDelegatedScopeCode(string $scopeCode): bool
    {
        return ScopeGeneratorInterface::DELEGATED_SCOPE_PREFIX === substr($scopeCode, 0, \strlen(ScopeGeneratorInterface::DELEGATED_SCOPE_PREFIX));
    }

    private function findDelegatedAccess(string $scopeCode): ?DelegatedAccess
    {
        $delegatedAccessUuid = substr($scopeCode, \strlen(ScopeGeneratorInterface::DELEGATED_SCOPE_PREFIX));

        return $this->delegatedAccessRepository->findOneByUuid($delegatedAccessUuid);
    }
}
