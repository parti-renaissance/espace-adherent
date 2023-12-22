<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Form\Renaissance\Adhesion\AdhesionMentionStep2Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/adhesion/cotisation', name: 'app_renaissance_adhesion_amount', methods: ['GET|POST'])]
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
            ->createForm(AdhesionMentionStep2Type::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('app_renaissance_adhesion_summary');
        }

        return $this->render('renaissance/adhesion/choose_amount.html.twig', [
            'form' => $form->createView(),
            'command' => $command,
        ]);
    }
}
