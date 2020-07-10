<?php

namespace App\Controller\EnMarche\CitizenProjects;

use App\Controller\AccessDelegatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent-partage/{delegated_access_uuid}/projets-citoyens", name="app_referent_citizen_projects_delegated_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_CITIZEN_PROJECTS', request)")
 */
class DelegatedReferentCitizenProjectsController extends ReferentCitizenProjectsController
{
    use AccessDelegatorTrait;
}
