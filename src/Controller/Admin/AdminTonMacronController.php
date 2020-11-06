<?php

namespace App\Controller\Admin;

use App\Repository\TonMacronChoiceRepository;
use App\Repository\TonMacronFriendInvitationRepository;
use App\TonMacron\TonMacronSerializer;
use Knp\Bundle\SnappyBundle\Snappy\Response\SnappyResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tonmacron")
 */
class AdminTonMacronController extends Controller
{
    const PER_PAGE = 1000;

    /**
     * @Route("/export/choices", name="app_admin_tonmacron_export_choices", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_TON_MACRON')")
     */
    public function exportChoicesAction(
        TonMacronSerializer $serializer,
        TonMacronChoiceRepository $repository
    ): Response {
        return new SnappyResponse(
            $serializer->serializeChoices($repository->findAll()),
            'tonmacron-choices.csv',
            'text/csv'
        );
    }

    /**
     * @Route("/export/invitations", name="app_admin_tonmacron_export_invitations", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_TON_MACRON')")
     */
    public function exportInvitationsAction(TonMacronFriendInvitationRepository $repository): Response
    {
        return $this->render('admin/ton_macron/invitation_export.html.twig', [
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
     * @Route("/export/invitations/partial", name="app_admin_tonmacron_export_invitations_partial", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_TON_MACRON')")
     */
    public function exportInvitationsPartialAction(
        Request $request,
        TonMacronSerializer $serializer,
        TonMacronFriendInvitationRepository $repository
    ): Response {
        $invitations = $repository->findPaginatedForExport($request->query->get('page', 1), self::PER_PAGE);

        return new JsonResponse([
            'count' => \count($invitations),
            'lines' => $serializer->serializeInvitations($invitations),
        ]);
    }
}
