<?php

namespace App\Controller\EnMarche\CitizenProjects;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/projets-citoyens", name="app_referent_citizen_projects_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_CITIZEN_PROJECTS'))")
 */
class ReferentCitizenProjectsController extends AbstractCitizenProjectsController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}
