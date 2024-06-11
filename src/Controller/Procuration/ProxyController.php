<?php

namespace App\Controller\Procuration;

use App\Controller\Procuration\Api\PersistEmailController;
use App\Entity\ProcurationV2\Election;
use App\Form\Procuration\V2\ProxyType;
use App\Procuration\V2\Command\ProxyCommand;
use App\Procuration\V2\ProcurationHandler;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route(path: '/{slug}/mandataire', name: 'app_procuration_v2_proxy', methods: ['GET', 'POST'])]
class ProxyController extends AbstractController
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

        $proxyCommand = $this->getProxyCommand($request);

        $form = $this
            ->createForm(ProxyType::class, $proxyCommand, [
                'election' => $election,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();

            if (
                !$session->has(PersistEmailController::SESSION_KEY)
                || $proxyCommand->email !== $session->get(PersistEmailController::SESSION_KEY)
            ) {
                $session->remove(PersistEmailController::SESSION_KEY);

                return $this->redirectToRoute('app_procuration_v2_proxy', ['slug' => $election->slug]);
            }

            $procurationProxy = $this->procurationHandler->handleProxy($proxyCommand);

            $session->remove(PersistEmailController::SESSION_KEY);

            return $this->redirectToRoute('app_procuration_v2_proxy_thanks', [
                'uuid' => $procurationProxy->getUuid(),
            ]);
        }

        return $this->renderForm('procuration_v2/proxy_form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'election' => $election,
            'step' => $this->step,
        ]);
    }

    public function getProxyCommand(Request $request): ProxyCommand
    {
        $proxy = new ProxyCommand();
        $proxy->clientIp = $request->getClientIp();

        return $proxy;
    }
}
