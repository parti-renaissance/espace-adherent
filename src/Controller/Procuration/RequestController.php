<?php

namespace App\Controller\Procuration;

use App\Controller\CanaryControllerTrait;
use App\Entity\Procuration\Election;
use App\Form\Procuration\V2\RequestType;
use App\Procuration\V2\Command\RequestCommand;
use App\Procuration\V2\ProcurationHandler;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route(path: '/{slug}/mandant', name: 'app_procuration_v2_request', methods: ['GET', 'POST'])]
class RequestController extends AbstractController
{
    use CanaryControllerTrait;

    private int $step = 0;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly AnonymousFollowerSession $anonymousFollowerSession,
        private readonly ProcurationHandler $procurationHandler
    ) {
    }

    public function __invoke(Request $request, Election $election): Response
    {
        $this->disableInProduction();

        if ($response = $this->anonymousFollowerSession->start($request)) {
            return $response;
        }

        $requestCommand = $this->getRequestCommand();

        $form = $this
            ->createForm(RequestType::class, $requestCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $procurationRequest = $this->procurationHandler->handleRequest($requestCommand);

            return $this->redirectToRoute('app_procuration_v2_request_thanks', [
                'uuid' => $procurationRequest->getUuid(),
            ]);
        }

        return $this->renderForm('procuration_v2/request_form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'step' => $this->step,
        ]);
    }

    private function getRequestCommand(): RequestCommand
    {
        return new RequestCommand();
    }
}
