<?php

namespace App\Controller\EnMarche\CommitteeDesignation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

#[ParamConverter('committee', options: ['mapping' => ['committee_slug' => 'slug']])]
#[Route(path: '/espace-animateur/{committee_slug}/designations', name: 'app_supervisor_designations')]
#[Security("is_granted('MANAGE_COMMITTEE_DESIGNATIONS', committee) and committee.isApproved()")]
class SupervisorDesignationController extends AbstractDesignationController
{
    protected function getSpaceType(): string
    {
        return 'supervisor';
    }
}
