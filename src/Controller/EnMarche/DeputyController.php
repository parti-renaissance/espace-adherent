<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Deputy\DeputyMessage;
use AppBundle\Deputy\DeputyMessageNotifier;
use AppBundle\Form\DeputyMessageType;
use AppBundle\Referent\ManagedCommitteesExporter;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-depute")
 * @Security("is_granted('ROLE_DEPUTY')")
 */
class DeputyController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/utilisateurs/message", name="app_deputy_users_message", methods={"GET", "POST"})
     */
    public function usersSendMessageAction(Request $request, AdherentRepository $adherentRepository): Response
    {
        $this->disableInProduction();

        $currentUser = $this->getUser();

        $message = DeputyMessage::create($this->getUser());

        $form = $this
            ->createForm(DeputyMessageType::class, $message)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(DeputyMessageNotifier::class)->sendMessage($message);
            $this->addFlash('info', 'deputy.message.success');

            return $this->redirectToRoute('app_deputy_users_message');
        }

        return $this->render('deputy/users_message.html.twig', [
            'results_count' => $adherentRepository->countAllInDistrict($currentUser->getManagedDistrict()),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comites", name="app_deputy_committees", methods={"GET"})
     */
    public function listCommitteesAction(
        CommitteeRepository $committeeRepository,
        ManagedCommitteesExporter $committeesExporter
    ): Response {
        $this->disableInProduction();

        return $this->render('deputy/committees_list.html.twig', [
            'managedCommitteesJson' => $committeesExporter->exportAsJson(
                $committeeRepository->findAllInDistrict($this->getUser()->getManagedDistrict())
            ),
        ]);
    }
}
