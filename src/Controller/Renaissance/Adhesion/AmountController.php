<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\MembershipRequestAmountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/contribution", name="app_renaissance_adhesion_amount", methods={"GET|POST"})
 */
class AmountController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $membershipRequestCommand = $this->storage->getMembershipRequestCommand();

        if (!$this->processor->canChooseAmount($membershipRequestCommand)) {
            return $this->redirectToRoute('app_renaissance_adhesion');
        }

        if ($request->query->has('back') && $this->processor->canFillPersonalInfo($membershipRequestCommand)) {
            return $this->redirectToRoute('app_renaissance_adhesion');
        }

        $form = $this
            ->createForm(
                MembershipRequestAmountType::class,
                $membershipRequestCommand
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->processor->canAcceptTermsAndConditions($membershipRequestCommand)) {
                return $this->redirectToRoute('app_renaissance_adhesion_amount');
            }

            $membershipRequestCommand->setClientIp($request->getClientIp());
            $this->processor->doAcceptTermsAndConditions($membershipRequestCommand);

            return $this->redirectToRoute('app_renaissance_adhesion_mentions');
        }

        return $this->render('renaissance/adhesion/choose_amount.html.twig', [
            'form' => $form->createView(),
            'membershipRequest' => $membershipRequestCommand,
        ]);
    }
}
