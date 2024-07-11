<?php

namespace App\Controller\Admin;

use App\Interactive\MyEuropeSerializer;
use App\Repository\MyEuropeChoiceRepository;
use App\Repository\MyEuropeInvitationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADMIN_MY_EUROPE')]
#[Route(path: '/myeurope')]
class AdminMyEuropeController extends AbstractController
{
    public const PER_PAGE = 1000;

    #[Route(path: '/export/choices', name: 'app_admin_myeurope_export_choices', methods: ['GET'])]
    public function exportChoicesAction(MyEuropeSerializer $serializer, MyEuropeChoiceRepository $repository): Response
    {
        $exported = $serializer->serializeChoices($repository->findAll());

        $response = new Response($exported);
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'my-europe-choices.csv'
        ));

        return $response;
    }

    #[Route(path: '/export/invitations', name: 'app_admin_myeurope_export_invitations', methods: ['GET'])]
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

    #[Route(path: '/export/invitations/partial', name: 'app_admin_myeurope_export_invitations_partial', methods: ['GET'])]
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
