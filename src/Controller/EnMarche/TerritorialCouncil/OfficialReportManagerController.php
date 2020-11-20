<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Entity\TerritorialCouncil\OfficialReport;
use App\Form\TerritorialCouncil\OfficialReportType;
use App\Repository\TerritorialCouncil\OfficialReportRepository;
use App\TerritorialCouncil\OfficialReportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/instances/proces-verbaux", name="app_instances_official_report_referent_")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class OfficialReportManagerController extends AbstractController
{
    /**
     * @Route("", name="list", methods={"GET"})
     */
    public function listAction(Request $request, OfficialReportRepository $repository): Response
    {
        return $this->render(
            'referent/territorial_council/official_report_list.html.twig',
            ['paginator' => $repository->getPaginator(
                $this->getUser()->getManagedArea()->getTags()->toArray(),
                $request->query->getInt('page', 1)
            )]
        );
    }

    /**
     * @Route("/creer", name="create", methods={"GET", "POST"})
     */
    public function createAction(Request $request, OfficialReportManager $manager): Response
    {
        $form = $this
            ->createForm(OfficialReportType::class, $report = new OfficialReport())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$report->getAuthor()
                && !$report->getPoliticalCommittee()->getTerritorialCouncil()->getMemberships()->getPresident()) {
                $this->addFlash('error', 'Vous ne pouvez pas créer un procès-verbal du Comité politique sans président.');

                return $this->redirectToRoute('app_instances_official_report_referent_list');
            }

            $manager->handleRequest($report);

            $this->addFlash('info', 'Le procès-verbal a été créé avec succès.');

            return $this->redirectToRoute('app_instances_official_report_referent_list');
        }

        return $this->render(
            'referent/territorial_council/official_report_create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @Route("/{uuid}/modifier", name="update", methods={"GET", "POST"})
     * @Security("is_granted('CAN_EDIT_OFFICIAL_REPORT', officialReport)")
     */
    public function updateAction(
        Request $request,
        OfficialReport $officialReport,
        OfficialReportManager $manager
    ): Response {
        $form = $this
            ->createForm(OfficialReportType::class, $officialReport)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->handleRequest($officialReport);

            $this->addFlash('info', 'Le procès-verbal a été modifié avec succès.');

            return $this->redirectToRoute('app_instances_official_report_referent_list');
        }

        return $this->render(
            'referent/territorial_council/official_report_create.html.twig',
            ['form' => $form->createView()]
        );
    }
}
