<?php

namespace App\Controller\EnMarche\Committee;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-referent/comites", name="app_referent_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentCommitteeController extends AbstractCommitteeController
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
