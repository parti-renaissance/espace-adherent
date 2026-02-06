<?php

declare(strict_types=1);

namespace App\Controller\Procuration;

use App\Controller\Procuration\Api\PersistEmailController;
use App\Entity\Procuration\Election;
use App\Form\Procuration\ProxyType;
use App\Procuration\Command\ProxyCommand;
use App\Procuration\ProcurationHandler;
use App\Security\Http\Session\AnonymousFollowerSession;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route(path: '/{slug}/mandataire', name: 'app_procuration_proxy', methods: ['GET', 'POST'])]
class ProxyController extends AbstractController
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

        $proxyCommand = $this->getProxyCommand($request, $election);

        $form = $this
            ->createForm(ProxyType::class, $proxyCommand, [
                'election' => $election,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $session = $request->getSession();

            $slugify = Slugify::create();

            if (
                !$session->has(PersistEmailController::SESSION_KEY)
                || $slugify->slugify($proxyCommand->email) !== $slugify->slugify($session->get(PersistEmailController::SESSION_KEY))
            ) {
                $session->remove(PersistEmailController::SESSION_KEY);

                return $this->redirectToRoute('app_procuration_proxy', ['slug' => $election->slug]);
            }

            $procurationProxy = $this->procurationHandler->handleProxy($proxyCommand);

            $session->remove(PersistEmailController::SESSION_KEY);

            return $this->redirectToRoute('app_procuration_proxy_thanks', [
                'uuid' => $procurationProxy->getUuid(),
            ]);
        }

        return $this->render('procuration/proxy_form.html.twig', [
            'form' => $form->createView(),
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'election' => $election,
            'step' => $this->step,
        ]);
    }

    public function getProxyCommand(Request $request, Election $election): ProxyCommand
    {
        $proxy = new ProxyCommand();
        $proxy->clientIp = $request->getClientIp();

        if (1 === $election->rounds->count()) {
            $proxy->rounds->add($election->rounds->first());
        }

        return $proxy;
    }
}
