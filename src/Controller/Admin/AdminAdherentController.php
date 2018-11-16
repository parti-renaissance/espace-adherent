<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Adherent\AdherentManagementAuthority;
use AppBundle\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminAdherentController extends Controller
{
    /**
     * Exit impersonation.
     *
     * @Route("/impersonation/exit", name="app_admin_impersonation_exit")
     * @Method("GET")
     */
    public function exitImpersonationAction(): Response
    {
        $authUtils = $this->get('app.security.authentication_utils');
        $impersonatingUser = $authUtils->getImpersonatingUser();

        if (!$impersonatingUser) {
            return $this->redirectToRoute('homepage');
        }

        $authUtils->authenticateAdmin($impersonatingUser);

        return $this->redirectToRoute('admin_app_adherent_list');
    }

    /**
     * @Route("/adherent/{id}/ban", name="app_admin_ban_adherent")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN_BAN')")
     */
    public function banAction(Request $request, Adherent $adherent, AdherentManagementAuthority $adherentManagementAuthority): Response
    {
        if (!$this->isCsrfTokenValid('app_admin_ban_adherent', $request->request->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        if (!$adherentManagementAuthority->canBan($adherent)) {
            $this->addFlash('error', sprintf('Vous ne pouvez bannir un adhérent qui a les rôles suivants (%s)', implode(', ', $adherent->getRoles())));

            return $this->redirectToRoute('admin_app_adherent_edit', [
                'id' => $adherent->getId(),
            ]);
        }

        $adherentManagementAuthority->ban($adherent);

        $this->addFlash('success', sprintf('L\'adhérent %s a bien été banni', $adherent->getFullName()));

        return $this->redirectToRoute('admin_app_adherent_list');
    }
}
