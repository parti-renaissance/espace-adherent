<?php

namespace App\Controller\Renaissance;

use App\Controller\CanaryControllerTrait;
use App\Form\MembershipRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/v2/adhesion', name: 'app_adhesion_index', methods: ['GET', 'POST'])]
class AdhesionController extends AbstractController
{
    use CanaryControllerTrait;

    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    public function __invoke(Request $request): Response
    {
        $this->disableInProduction();

        $form = $this
            ->createForm(MembershipRequestType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirect('app_adhesion_index');
        }

        return $this->renderForm('renaissance/adhesion/form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
        ]);
    }
}
