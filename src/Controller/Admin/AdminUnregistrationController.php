<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Repository\UnregistrationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function exportUnregistrationsAction(UnregistrationRepository $repository): Response
    {
        return $this->render('admin/adherent/unregistration_export.html.twig', [
            'total_count' => $repository->countForExport(),
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
    public function exportUnregistrationsPartialAction(Request $request, UnregistrationRepository $repository): Response
    {
        $unregistrations = $repository->findPaginatedForExport($request->query->getInt('page', 1), self::PER_PAGE);

        return new JsonResponse([
            'count' => \count($unregistrations),
            'lines' => $this->get('app.unregistration.serializer')->serialize($unregistrations),
        ]);
    }
}
