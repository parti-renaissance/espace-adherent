<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Unregistration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/unregistration")
 */
class AdminUnregistrationController extends Controller
{
    const PER_PAGE = 1000;

    /**
     * @Route("/export", name="app_admin_unregistration_export")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_UNREGISTRATIONS')")
     */
    public function exportUnregistrationsAction(): Response
    {
        return $this->render('admin/adherent/unregistration_export.html.twig', [
            'total_count' => $this->getDoctrine()->getRepository(Unregistration::class)->countForExport(),
            'csv_header' => implode(',', [
                'uuid',
                'postalCode',
                'reasons',
                'comment',
                'registeredAt',
                'unregisteredAt',
            ]),
        ]);
    }

    /**
     * @Route("/export/partial", name="app_admin_unregistration_export_partial")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_UNREGISTRATIONS')")
     */
    public function exportUnregistrationsPartialAction(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        $manager = $this->getDoctrine()->getManager();
        $unregistrations = $manager->getRepository(Unregistration::class)->findPaginatedForExport($page, self::PER_PAGE);

        return new JsonResponse([
            'count' => count($unregistrations),
            'lines' => $this->get('app.unregistration.serializer')->serialize($unregistrations),
        ]);
    }
}
