<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Scope\Exception\NotFoundScopeGeneratorException;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;

trait ScopeTrait
{
    /** @var GeneralScopeGenerator */
    protected $generalScopeGenerator;

    protected function getScope(string $scopeCode, Adherent $adherent): Scope
    {
        try {
            return $this->generalScopeGenerator->getGenerator($scopeCode, $adherent)->generate($adherent);
        } catch (NotFoundScopeGeneratorException $e) {
            // Catch for throwing AccessDenied exception
        }

        throw $this->createAccessDeniedException('User has no required scope.');
    }
}
