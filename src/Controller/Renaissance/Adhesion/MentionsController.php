<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\MembershipRequestMentionsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/mentions", name="app_renaissance_adhesion_mentions", methods={"GET|POST"})
 */
class MentionsController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $membershipRequestCommand = $this->storage->getMembershipRequestCommand();

        if (!$this->processor->canAcceptTermsAndConditions($membershipRequestCommand)) {
            return $this->redirectToRoute('app_renaissance_adhesion_amount');
        }

        if ($request->query->has('back') && $this->processor->canChooseAmount($membershipRequestCommand)) {
            $this->processor->doChooseAmount($membershipRequestCommand);

            return $this->redirectToRoute('app_renaissance_adhesion_amount');
        }

        $form = $this
            ->createForm(
                MembershipRequestMentionsType::class,
                $membershipRequestCommand
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->processor->canValidSummary($membershipRequestCommand)) {
                return $this->redirectToRoute('app_renaissance_adhesion_mentions');
            }

            $this->processor->doValidSummary($membershipRequestCommand);

            return $this->redirectToRoute('app_renaissance_adhesion_summary');
        }

        return $this->render('renaissance/adhesion/mentions.html.twig', [
            'form' => $form->createView(),
            'membershipRequest' => $membershipRequestCommand,
        ]);
    }
}
