<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\MembershipRequestAmountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/adhesion/contribution", name="app_renaissance_adhesion_amount", methods={"GET|POST"})
 */
class AmountController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canChooseAmount($command)) {
            return $this->redirectToRoute('app_renaissance_adhesion');
        }

        $this->processor->doChooseAmount($command);

        $form = $this
            ->createForm(MembershipRequestAmountType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processor->doChooseAmount($command);

            return $this->redirectToRoute('app_renaissance_adhesion_mentions');
        }

        return $this->render('renaissance/adhesion/choose_amount.html.twig', [
            'form' => $form->createView(),
            'command' => $command,
        ]);
    }
}
