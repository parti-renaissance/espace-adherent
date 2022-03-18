<?php

namespace App\Controller\Procuration;

use App\Entity\Adherent;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Form\Procuration\ElectionContextType;
use App\Form\Procuration\ProcurationProxyType;
use App\Form\Procuration\ProcurationRequestType;
use App\Procuration\ElectionContext;
use App\Procuration\ProcurationManager;
use App\Procuration\ProcurationSession;
use App\Repository\ElectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProcurationController extends AbstractController
{
    /**
     * @Route(name="app_procuration_landing", methods={"GET"})
     */
    public function landingAction(
        ProcurationSession $procurationSession,
        ElectionRepository $electionRepository
    ): Response {
        $procurationSession->endRequest(); // Back to landing should reset the flow

        return $this->render('procuration/landing.html.twig', [
            'next_election' => $electionRepository->findComingNextElection(),
        ]);
    }

    /**
     * @Route(
     *     "/choisir/{action}",
     *     requirements={"action": App\Procuration\ElectionContext::CONTROLLER_ACTION_REQUIREMENT},
     *     name="app_procuration_choose_election",
     *     methods={"GET", "POST"}
     * )
     */
    public function chooseElectionAction(
        Request $request,
        string $action,
        ProcurationSession $procurationSession
    ): Response {
        $form = $this->createForm(ElectionContextType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $procurationSession->setElectionContext($form->getData());

            if (ElectionContext::ACTION_REQUEST === $action) {
                return $this->redirectToRoute('app_procuration_request', ['step' => ProcurationRequest::STEP_URI_VOTE]);
            }

            return $this->redirectToRoute('app_procuration_proxy_proposal');
        }

        return $this->render('procuration/choose_election.html.twig', [
            'action' => $action,
            'elections_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande", name="app_procuration_index_legacy", methods={"GET", "POST"})
     * @Route(
     *     "/je-demande/{step}",
     *     requirements={"step": "mon-lieu-de-vote|mes-coordonnees|ma-procuration"},
     *     name="app_procuration_request"
     * )
     */
    public function requestAction(
        Request $request,
        ProcurationSession $procurationSession,
        ProcurationManager $procurationManager,
        ?string $step,
        string $_route
    ): Response {
        if ('app_procuration_index_legacy' === $_route) {
            return $this->redirectToRoute('app_procuration_request', ['step' => ProcurationRequest::STEP_URI_VOTE], Response::HTTP_MOVED_PERMANENTLY);
        }

        if (!$procurationSession->hasElectionContext()) {
            return $this->redirectToRoute('app_procuration_choose_election', ['action' => ElectionContext::ACTION_REQUEST]);
        }

        $user = $this->getUser();
        $procurationRequest = $procurationSession->getCurrentRequest();

        if (ProcurationRequest::STEP_URI_PROFILE === $step && $user instanceof Adherent) {
            $procurationRequest->importAdherentData($user);
        }

        if ($finalStep = ProcurationRequest::isFinalStepUri($step)) {
            $procurationRequest->setRecaptcha($request->request->get('g-recaptcha-response'));
        }

        $form = $this->createForm(ProcurationRequestType::class, $procurationRequest, [
            'step_uri' => $step,
            'election_context' => $procurationSession->getElectionContext(),
        ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($finalStep) {
                $procurationRequest->setEnabled(true);
                $procurationManager->createProcurationRequest($procurationRequest);

                $procurationSession->endRequest();

                return $this->redirectToRoute('app_procuration_request_thanks');
            }

            return $this->redirectToRoute('app_procuration_request', ['step' => ProcurationRequest::getNextStepUri($step)]);
        }

        return $this->render(sprintf('procuration/request/%s.html.twig', ProcurationRequest::getStepForUri($step)), [
            'procuration_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/merci", name="app_procuration_request_thanks", methods={"GET"})
     */
    public function requestThanksAction(): Response
    {
        return $this->render('procuration/request/thanks.html.twig');
    }

    /**
     * @Route("/je-propose", name="app_procuration_proxy_proposal", methods={"GET", "POST"})
     */
    public function proxyProposalAction(
        Request $request,
        ProcurationSession $procurationSession,
        ProcurationManager $procurationManager
    ): Response {
        if (!$procurationSession->hasElectionContext()) {
            return $this->redirectToRoute('app_procuration_choose_election', ['action' => ElectionContext::ACTION_PROPOSAL]);
        }

        $proposal = new ProcurationProxy();
        $proposal->setRecaptcha($request->request->get('g-recaptcha-response'));

        $user = $this->getUser();

        if ($user instanceof Adherent) {
            $proposal->importAdherentData($user);
        }

        $form = $this->createForm(ProcurationProxyType::class, $proposal, [
            'election_context' => $procurationSession->getElectionContext(),
        ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $procurationManager->createProcurationProxy($proposal);

            return $this->redirectToRoute('app_procuration_proposal_thanks');
        }

        return $this->render('procuration/proxy/proposal.html.twig', [
            'procuration_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-propose/merci", name="app_procuration_proposal_thanks", methods={"GET"})
     */
    public function proposalThanksAction(): Response
    {
        return $this->render('procuration/proxy/thanks.html.twig');
    }

    /**
     * @Route("/ma-demande/{id}/{privateToken}", requirements={"privateToken": "%pattern_uuid%"}, name="app_procuration_my_request", methods={"GET"})
     */
    public function myRequestAction(ProcurationRequest $request, string $privateToken): Response
    {
        if ($privateToken !== $request->generatePrivateToken()) {
            throw $this->createNotFoundException('Invalid token.');
        }

        if (!$request->isEnabled()) {
            throw new BadRequestHttpException();
        }

        return $this->render('procuration/request/my_request.html.twig', [
            'request' => $request,
            'proxy' => $request->getFoundProxy(),
        ]);
    }
}
