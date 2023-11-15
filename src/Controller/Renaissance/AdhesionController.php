<?php

namespace App\Controller\Renaissance;

use App\Form\MembershipRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v2/adhesion', name: 'app_adhesion_')]
class AdhesionController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function formAction(Request $request): Response
    {
        $form = $this
            ->createForm(MembershipRequestType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirect('app_adhesion_index');
        }

        return $this->renderForm('renaissance/adhesion/form.html.twig', ['form' => $form]);
    }
}
