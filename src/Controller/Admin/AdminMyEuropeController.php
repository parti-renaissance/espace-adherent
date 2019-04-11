<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\MyEuropeChoice;
use AppBundle\Entity\MyEuropeInvitation;
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
    public function exportChoicesAction(): Response
    {
        $choices = $this->getDoctrine()->getRepository(MyEuropeChoice::class)->findAll();
        $exported = $this->get('app.my_europe.serializer.serializer')->serializeChoices($choices);

        return new SnappyResponse($exported, 'my-europe-choices.csv', 'text/csv');
    }

    /**
     * @Route("/export/invitations", name="app_admin_myeurope_export_invitations", methods={"GET"})
     */
    public function exportInvitationsAction(): Response
    {
        return $this->render('admin/interactive/invitation_export.html.twig', [
            'total_count' => $this->getDoctrine()->getRepository(MyEuropeInvitation::class)->countForExport(),
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
    public function exportInvitationsPartialAction(Request $request): Response
    {
        $page = $request->query->get('page', 1);

        $manager = $this->getDoctrine()->getManager();
        $invitations = $manager->getRepository(MyEuropeInvitation::class)->findPaginatedForExport($page, self::PER_PAGE);

        return new JsonResponse([
            'count' => \count($invitations),
            'lines' => $this->get('app.my_europe.serializer')->serializeInvitations($invitations),
        ]);
    }
}
