<?php

namespace App\Controller\EnMarche\CitizenProjects;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-referent/projets-citoyens", name="app_referent_citizen_projects_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentCitizenProjectsController extends AbstractCitizenProjectsController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }

    protected function getMainUser(Request $request): UserInterface
    {
        return $this->getUser();
    }
}
