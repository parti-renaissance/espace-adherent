<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adherent;
use AppBundle\Form\ProcurationProfileType;
use AppBundle\Form\ProcurationElectionsType;
use AppBundle\Form\ProcurationVoteType;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Procuration\ProcurationRequestFlow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/procuration")
 */
class ProcurationController extends Controller
{
    /**
     * @Route("", name="app_procuration_index")
     * @Method("GET")
     */
    public function indexAction(Request $request): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $this->getProcurationFlow()->reset();

        return $this->render('procuration/index.html.twig', [
            'has_error' => $request->query->getBoolean('has_error'),
            'form' => $this->createForm(ProcurationVoteType::class, new ProcurationRequest())->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/mon-lieu-de-vote", name="app_procuration_request_vote")
     * @Method("GET|POST")
     */
    public function voteAction(Request $request): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $command = $this->getProcurationFlow()->getCurrentModel();

        $form = $this->createForm(ProcurationVoteType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getProcurationFlow()->save($command);

            return $this->redirectToRoute('app_procuration_request_profile');
        }

        return $this->render('procuration/vote.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/mes-coordonnees", name="app_procuration_request_profile")
     * @Method("GET|POST")
     */
    public function profileAction(Request $request): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $command = $this->getProcurationFlow()->getCurrentModel();

        if ($this->getUser() instanceof Adherent) {
            $command->importAdherentData($this->getUser());
        }

        $form = $this->createForm(ProcurationProfileType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getProcurationFlow()->save($command);

            return $this->redirectToRoute('app_procuration_request_elections');
        }

        return $this->render('procuration/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/ma-procuration", name="app_procuration_request_elections")
     * @Method("GET|POST")
     */
    public function electionsAction(Request $request): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        $command = $this->getProcurationFlow()->getCurrentModel();
        $command->recaptcha = (string) $request->request->get('g-recaptcha-response');

        $form = $this->createForm(ProcurationElectionsType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($command);
            $manager->flush();

            $this->getProcurationFlow()->reset();

            return $this->redirectToRoute('app_procuration_request_thanks');
        }

        return $this->render('procuration/elections.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/merci", name="app_procuration_request_thanks")
     * @Method("GET")
     */
    public function thanksAction(): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        return $this->render('procuration/thanks.html.twig');
    }

    private function getProcurationFlow(): ProcurationRequestFlow
    {
        return $this->get('app.procuration.request_flow');
    }
}
