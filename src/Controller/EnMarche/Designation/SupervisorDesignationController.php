<?php

namespace App\Controller\EnMarche\Designation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-animateur/{committee_slug}/designations", name="app_supervisor_designations")
 *
 * @ParamConverter("committee", options={"mapping": {"committee_slug": "slug"}})
 *
 * @Security("is_granted('SUPERVISE_COMMITTEE', committee) and committee.isApproved()")
 */
class SupervisorDesignationController extends AbstractDesignationController
{
    protected function getRouteParameters(Request $request): array
    {
        return [
            'committee_slug' => $request->attributes->get('committee_slug'),
        ];
    }

    protected function getSpaceType(): string
    {
        return 'supervisor';
    }
}
