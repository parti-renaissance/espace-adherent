<?php

namespace AppBundle\Controller;

use AppBundle\Form\ProcurationCitySelectorType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
            $forms[$type] = $this->createForm(ProcurationCitySelectorType::class, ['country' => 'FR'])->createView();
        }

        return $this->render('procuration/index.html.twig', [
            'forms' => $forms,
        ]);
    }

    /**
     * @Route("/je-demande/mon-adresse", name="app_procuration_request_address")
     * @Method("GET")
     */
    public function startAction(): Response
    {
        // TODO
    }
}
