<?php

declare(strict_types=1);

namespace App\Controller\Procuration;

use App\Controller\Procuration\Api\PersistEmailController;
use App\Entity\Procuration\Election;
use App\Form\Procuration\RequestType;
use App\Procuration\Command\RequestCommand;
use App\Procuration\ProcurationHandler;
use App\Security\Http\Session\AnonymousFollowerSession;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route(path: '/{slug}/mandant', name: 'app_procuration_request', methods: ['GET', 'POST'])]
class RequestController extends AbstractController
{
    private int $step = 0;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly AnonymousFollowerSession $anonymousFollowerSession,
        private readonly ProcurationHandler $procurationHandler,
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

        $requestCommand = $this->getRequestCommand($request, $election);

        $form = $this
            ->createForm(RequestType::class, $requestCommand, [
                'election' => $election,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();

            $slugify = Slugify::create();

            if (
                !$session->has(PersistEmailController::SESSION_KEY)
                || $slugify->slugify($requestCommand->email) !== $slugify->slugify($session->get(PersistEmailController::SESSION_KEY))
            ) {
                $session->remove(PersistEmailController::SESSION_KEY);

                return $this->redirectToRoute('app_procuration_request', ['slug' => $election->slug]);
            }

            $procurationRequest = $this->procurationHandler->handleRequest($requestCommand);

            $session->remove(PersistEmailController::SESSION_KEY);

            return $this->redirectToRoute('app_procuration_request_thanks', [
                'uuid' => $procurationRequest->getUuid(),
            ]);
        }

        return $this->render('procuration/request_form.html.twig', [
            'form' => $form->createView(),
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'election' => $election,
            'step' => $this->step,
        ]);
    }

    private function getRequestCommand(Request $request, Election $election): RequestCommand
    {
        $requestCommand = new RequestCommand();
        $requestCommand->clientIp = $request->getClientIp();

        if (1 === $election->rounds->count()) {
            $requestCommand->rounds->add($election->rounds->first());
        }

        return $requestCommand;
    }
}
