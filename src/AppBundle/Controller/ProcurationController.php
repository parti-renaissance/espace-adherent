<?php

namespace AppBundle\Controller;

use AppBundle\Form\ProcurationAddressType;
use AppBundle\Form\ProcurationElectionsType;
use AppBundle\Form\ProcurationVoteType;
use AppBundle\Procuration\ProcurationRequestCommand;
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
    public function indexAction(): Response
    {
        $forms = [];

        foreach (['header', 'footer'] as $type) {
            $forms[$type] = $this
                ->createForm(ProcurationVoteType::class, new ProcurationRequestCommand())
                ->createView()
            ;
        }

        return $this->render('procuration/index.html.twig', [
            'forms' => $forms,
        ]);
    }

    /**
     * @Route("/je-demande/adresse", name="app_procuration_request_address")
     * @Method("POST")
     */
    public function addressAction(Request $request): Response
    {
        $procurationRequestCommand = new ProcurationRequestCommand();

        $form = $this->createForm(ProcurationVoteType::class, $procurationRequestCommand);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->redirectToRoute('app_procuration_index');
        }

        return $this->render('procuration/address.html.twig', [
            'form' => $this->createForm(ProcurationAddressType::class, $procurationRequestCommand)->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/elections", name="app_procuration_request_elections")
     * @Method("POST")
     */
    public function electionsAction(Request $request): Response
    {
        $procurationRequestCommand = new ProcurationRequestCommand();

        $form = $this->createForm(ProcurationAddressType::class, $procurationRequestCommand);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->redirectToRoute('app_procuration_index');
        }

        return $this->render('procuration/elections.html.twig', [
            'form' => $this->createForm(ProcurationElectionsType::class, $procurationRequestCommand)->createView(),
        ]);
    }

    /**
     * @Route("/je-demande/coordonnees", name="app_procuration_request_profile")
     * @Method("POST")
     */
    public function profileAction(Request $request): Response
    {
        $procurationRequestCommand = new ProcurationRequestCommand();

        $form = $this->createForm(ProcurationElectionsType::class, $procurationRequestCommand);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {

            dump($form);
            exit;
            return $this->redirectToRoute('app_procuration_index');
        }

        dump($procurationRequestCommand);
        exit;

        return $this->render('procuration/index.html.twig', [
            'form' => $this->createForm(ProcurationAddressType::class, $procurationRequestCommand)->createView(),
        ]);
    }
}
