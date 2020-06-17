<?php

namespace App\Controller\EnMarche;

use App\Controller\CanaryControllerTrait;
use App\Deputy\DeputyMessage;
use App\Deputy\DeputyMessageNotifier;
use App\Form\DeputyMessageType;
use App\Referent\ManagedCommitteesExporter;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-depute", name="app_deputy_")
 * @Security("is_granted('ROLE_DEPUTY')")
 */
class DeputyController extends Controller
{
    use CanaryControllerTrait;

    public function getSpaceType(): string
    {
        return 'deputy';
    }

    /**
     * @Route("/utilisateurs/message", name="users_message", methods={"GET", "POST"})
     */
    public function usersSendMessageAction(Request $request, AdherentRepository $adherentRepository): Response
    {
        $this->disableInProduction();

        $currentUser = $this->getMainUser($request);

        $message = DeputyMessage::create($currentUser);

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
     * @Route("/comites", name="committees", methods={"GET"})
     */
    public function listCommitteesAction(
        Request $request,
        CommitteeRepository $committeeRepository,
        ManagedCommitteesExporter $committeesExporter
    ): Response {
        return $this->render('deputy/committees_list.html.twig', [
            'managedCommitteesJson' => $committeesExporter->exportAsJson(
                $committeeRepository->findAllInDistrict($this->getMainUser($request)->getManagedDistrict())
            ),
        ]);
    }

    protected function getMainUser(Request $request)
    {
        return $this->getUser();
    }
}
