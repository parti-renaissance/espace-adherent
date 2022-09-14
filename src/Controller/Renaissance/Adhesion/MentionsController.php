<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\MembershipRequestMentionsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/adhesion/mentions", name="app_renaissance_adhesion_mentions", methods={"GET|POST"})
 */
class MentionsController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canAcceptTermsAndConditions($command)) {
            return $this->redirectToRoute('app_renaissance_adhesion_additional_informations');
        }

        $this->processor->doAcceptTermsAndConditions($command);

        $form = $this
            ->createForm(MembershipRequestMentionsType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_renaissance_adhesion_summary');
        }

        return $this->render('renaissance/adhesion/mentions.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
