<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\TurnkeyProject;
use AppBundle\TurnkeyProject\TurnkeyProjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/turnkey_projects")
 */
class AdminTurnkeyProjectController extends Controller
{
    /**
     * @Route("/{id}/remove-image", name="app_admin_turnkey_project_remove_image", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_TURNKEY_PROJECTS')")
     */
    public function removeImageAction(
        Request $request,
        TurnkeyProject $turnkeyProject,
        TurnkeyProjectManager $turnkeyProjectManager
    ): Response {
        if (!$this->isCsrfTokenValid(sprintf('turnkey_project.remove_image.%s', $turnkeyProject->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            $turnkeyProjectManager->removeImage($turnkeyProject);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('sonata_flash_success',
                sprintf('L\'image du projet clé en main « %s » a été supprimée avec succès.', $turnkeyProject->getName())
            );
        } catch (\LogicException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return $this->redirectToRoute('admin_app_turnkeyproject_list');
    }
}
