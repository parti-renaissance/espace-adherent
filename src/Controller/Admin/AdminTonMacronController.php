<?php

namespace App\Controller\Admin;

use App\Repository\TonMacronChoiceRepository;
use App\Repository\TonMacronFriendInvitationRepository;
use App\TonMacron\TonMacronSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/tonmacron')]
class AdminTonMacronController extends AbstractController
{
    public const PER_PAGE = 1000;

    #[IsGranted('ROLE_ADMIN_TON_MACRON')]
    #[Route(path: '/export/choices', name: 'app_admin_tonmacron_export_choices', methods: ['GET'])]
    public function exportChoicesAction(
        TonMacronSerializer $serializer,
        TonMacronChoiceRepository $repository
    ): Response {
        $response = new Response($serializer->serializeChoices($repository->findAll()));
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'tonmacron-choices.csv'
        ));

        return $response;
    }

    #[IsGranted('ROLE_ADMIN_TON_MACRON')]
    #[Route(path: '/export/invitations', name: 'app_admin_tonmacron_export_invitations', methods: ['GET'])]
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

    #[IsGranted('ROLE_ADMIN_TON_MACRON')]
    #[Route(path: '/export/invitations/partial', name: 'app_admin_tonmacron_export_invitations_partial', methods: ['GET'])]
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
