<?php

namespace App\Controller\Procuration;

use App\Controller\CanaryControllerTrait;
use App\Entity\Procuration\Election;
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

        $proxyCommand = $this->getProxyCommand();

        $form = $this
            ->createForm(ProxyType::class, $proxyCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $procurationProxy = $this->procurationHandler->handleProxy($proxyCommand);

            return $this->redirectToRoute('app_procuration_v2_proxy_thanks', [
                'uuid' => $procurationProxy->getUuid(),
            ]);
        }

        return $this->renderForm('procuration_v2/proxy_form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'step' => $this->step,
        ]);
    }

    public function getProxyCommand(): ProxyCommand
    {
        return new ProxyCommand();
    }
}
