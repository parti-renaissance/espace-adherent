<?php

declare(strict_types=1);

namespace App\Controller\EnMarche\CommitteeDesignation;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('MANAGE_COMMITTEE_DESIGNATIONS', subject) and subject.isApproved()"), subject: 'committee')]
#[Route(path: '/espace-animateur/{committee_slug}/designations', name: 'app_supervisor_designations')]
class SupervisorDesignationController extends AbstractDesignationController
{
    protected function getSpaceType(): string
    {
        return 'supervisor';
    }
}
