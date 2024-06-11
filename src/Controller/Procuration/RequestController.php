<?php

namespace App\Controller\Procuration;

use App\Controller\Procuration\Api\PersistEmailController;
use App\Entity\ProcurationV2\Election;
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
    private int $step = 0;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly AnonymousFollowerSession $anonymousFollowerSession,
        private readonly ProcurationHandler $procurationHandler
    ) {
    }

    public function __invoke(Request $request, Election $election): Response
    {
        if (!$election->getUpcomingRound()) {
            throw $this->createNotFoundException();
        }

        if ($response = $this->anonymousFollowerSession->start($request)) {
            return $response;
        }

        $requestCommand = $this->getRequestCommand($request);

        $form = $this
            ->createForm(RequestType::class, $requestCommand, [
                'election' => $election,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();

            if (
                !$session->has(PersistEmailController::SESSION_KEY)
                || $requestCommand->email !== $session->get(PersistEmailController::SESSION_KEY)
            ) {
                $session->remove(PersistEmailController::SESSION_KEY);

                return $this->redirectToRoute('app_procuration_v2_request', ['slug' => $election->slug]);
            }

            $procurationRequest = $this->procurationHandler->handleRequest($requestCommand);

            $session->remove(PersistEmailController::SESSION_KEY);

            return $this->redirectToRoute('app_procuration_v2_request_thanks', [
                'uuid' => $procurationRequest->getUuid(),
            ]);
        }

        return $this->renderForm('procuration_v2/request_form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'election' => $election,
            'step' => $this->step,
        ]);
    }

    private function getRequestCommand(Request $request): RequestCommand
    {
        $requestCommand = new RequestCommand();
        $requestCommand->clientIp = $request->getClientIp();

        return $requestCommand;
    }
}
