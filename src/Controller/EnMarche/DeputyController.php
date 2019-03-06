<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Form\DeputyMessageType;
use AppBundle\Deputy\DeputyMessage;
use AppBundle\Deputy\DeputyMessageNotifier;
use AppBundle\Referent\ManagedCommitteesExporter;
use AppBundle\Referent\ManagedEventsExporter;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-depute")
 * @Security("is_granted('ROLE_DEPUTY')")
 */
class DeputyController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/utilisateurs/message", name="app_deputy_users_message")
     * @Method("GET|POST")
     */
    public function usersSendMessageAction(Request $request, AdherentRepository $adherentRepository): Response
    {
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
     * @Route("/evenements", name="app_deputy_events")
     * @Method("GET")
     */
    public function listEventsAction(EventRepository $eventRepository, ManagedEventsExporter $eventsExporter): Response
    {
        $this->disableInProduction();

        return $this->render('deputy/events_list.html.twig', [
            'managedEventsJson' => $eventsExporter->exportAsJson($eventRepository->findAllInDistrict($this->getUser()->getManagedDistrict())),
        ]);
    }

    /**
     * @Route("/comites", name="app_deputy_committees")
     * @Method("GET")
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
