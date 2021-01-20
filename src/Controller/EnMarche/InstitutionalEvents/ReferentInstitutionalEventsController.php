<?php

namespace App\Controller\EnMarche\InstitutionalEvents;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/evenements-institutionnels", name="app_referent_institutional_events_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_INSTITUTIONAL_EVENTS', request))")
 */
class ReferentInstitutionalEventsController extends AbstractInstitutionalEventsController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}
