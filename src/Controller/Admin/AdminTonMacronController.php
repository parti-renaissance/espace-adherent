<?php

namespace App\Controller\Admin;

use App\Entity\TonMacronChoice;
use App\Entity\TonMacronFriendInvitation;
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
    public function exportChoicesAction(): Response
    {
        $choices = $this->getDoctrine()->getRepository(TonMacronChoice::class)->findAll();
        $exported = $this->get('app.ton_macron.serializer')->serializeChoices($choices);

        return new SnappyResponse($exported, 'tonmacron-choices.csv', 'text/csv');
    }

    /**
     * @Route("/export/invitations", name="app_admin_tonmacron_export_invitations", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_TON_MACRON')")
     */
    public function exportInvitationsAction(): Response
    {
        return $this->render('admin/ton_macron/invitation_export.html.twig', [
            'total_count' => $this->getDoctrine()->getRepository(TonMacronFriendInvitation::class)->countForExport(),
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
    public function exportInvitationsPartialAction(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        $manager = $this->getDoctrine()->getManager();
        $invitations = $manager->getRepository(TonMacronFriendInvitation::class)->findPaginatedForExport($page, self::PER_PAGE);

        return new JsonResponse([
            'count' => \count($invitations),
            'lines' => $this->get('app.ton_macron.serializer')->serializeInvitations($invitations),
        ]);
    }
}
