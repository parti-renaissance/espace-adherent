<?php

namespace App\Controller\Admin;

use App\Interactive\MyEuropeSerializer;
use App\Repository\MyEuropeChoiceRepository;
use App\Repository\MyEuropeInvitationRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\SnappyResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/myeurope")
 * @Security("has_role('ROLE_ADMIN_MY_EUROPE')")
 */
class AdminMyEuropeController extends Controller
{
    const PER_PAGE = 1000;

    /**
     * @Route("/export/choices", name="app_admin_myeurope_export_choices", methods={"GET"})
     */
    public function exportChoicesAction(MyEuropeSerializer $serializer, MyEuropeChoiceRepository $repository): Response
    {
        $exported = $serializer->serializeChoices($repository->findAll());

        return new SnappyResponse($exported, 'my-europe-choices.csv', 'text/csv');
    }

    /**
     * @Route("/export/invitations", name="app_admin_myeurope_export_invitations", methods={"GET"})
     */
    public function exportInvitationsAction(MyEuropeInvitationRepository $repository): Response
    {
        return $this->render('admin/interactive/invitation_export.html.twig', [
            'total_count' => $repository->countForExport(),
            'csv_header' => implode(',', [
                'id',
                'friend_firstName',
                'friend_age',
                'friend_gender',
                'friend_position',
                'friend_emailAddress',
                'author_firstName',
                'author_lastName',
                'author_emailAddress',
                'mail_subject',
                'date',
            ]),
        ]);
    }

    /**
     * @Route("/export/invitations/partial", name="app_admin_myeurope_export_invitations_partial", methods={"GET"})
     */
    public function exportInvitationsPartialAction(
        MyEuropeInvitationRepository $repository,
        Request $request,
        MyEuropeSerializer $serializer
    ): Response {
        $page = $request->query->get('page', 1);

        $invitations = $repository->findPaginatedForExport($page, self::PER_PAGE);

        return new JsonResponse([
            'count' => \count($invitations),
            'lines' => $serializer->serializeInvitations($invitations),
        ]);
    }
}
