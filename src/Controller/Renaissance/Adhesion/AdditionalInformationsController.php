<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\MembershipRequestAdditionalInformationsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/adhesion/informations-additionelles", name="app_renaissance_adhesion_additional_informations", methods={"GET|POST"})
 */
class AdditionalInformationsController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canFillAdditionalInformations($command)) {
            return $this->redirectToRoute('app_renaissance_adhesion_amount');
        }

        $this->processor->doFillAdditionalInformations($command);

        $form = $this
            ->createForm(MembershipRequestAdditionalInformationsType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processor->doAcceptTermsAndConditions($command);

            return $this->redirectToRoute('app_renaissance_adhesion_mentions');
        }

        return $this->render('renaissance/adhesion/additional_informations.html.twig', [
            'form' => $form->createView(),
            'command' => $command,
        ]);
    }
}
