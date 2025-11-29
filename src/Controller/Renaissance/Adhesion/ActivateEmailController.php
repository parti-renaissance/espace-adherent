<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\ActivationCodeManager;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Command\GenerateActivationCodeCommand;
use App\Adhesion\Exception\ActivationCodeExceptionInterface;
use App\Adhesion\Request\ValidateAccountRequest;
use App\Entity\Adherent;
use App\Form\ActivateEmailByCodeType;
use App\Form\ConfirmActionType;
use App\Utils\UtmParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

class ActivateEmailController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_confirm_email';

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly ActivationCodeManager $activationCodeManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly RateLimiterFactory $changeEmailLimiter,
    ) {
    }

    #[Route(path: '/adhesion/confirmation-email', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function validateAction(Request $request): Response
    {
        $utmParams = UtmParams::fromRequest($request);

        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME, $utmParams);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::ACTIVATION)) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $validateAccountRequest = new ValidateAccountRequest();

        $form = $this
            ->createForm(ActivateEmailByCodeType::class, $validateAccountRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('validate')->isClicked()) {
                try {
                    $this->activationCodeManager->validate((string) $validateAccountRequest->code, $adherent);
                    $this->addFlash('success', 'Votre adresse email a bien été validée !');

                    return $this->redirectToRoute(CreatePasswordController::ROUTE_NAME, $utmParams);
                } catch (ActivationCodeExceptionInterface $e) {
                    $form->get('code')->addError(new FormError($e->getMessage()));
                }
            } elseif ($form->get('changeEmail')->isClicked()) {
                $limiter = $this->changeEmailLimiter->create('change_email.'.$adherent->getId());

                if (!$limiter->consume()->isAccepted()) {
                    $this->addFlash('error', 'Veuillez patienter quelques minutes avant de retenter.');

                    return $this->redirectToRoute(self::ROUTE_NAME, $utmParams);
                }

                $adherent->setEmailAddress($validateAccountRequest->emailAddress);
                $this->entityManager->flush();
                $this->bus->dispatch(new GenerateActivationCodeCommand($adherent, true));

                $this->addFlash('success', 'Votre adresse email a bien été modifiée ! Veuillez saisir le nouveau code reçu par email.');

                return $this->redirectToRoute(self::ROUTE_NAME, $utmParams);
            }
        }

        return $this->render('renaissance/adhesion/confirmation_email.html.twig', [
            'code_ttl' => ActivationCodeManager::CODE_TTL,
            'request' => $validateAccountRequest,
            'form' => $form->createView(),
            'utm_params' => $utmParams,
            'new_code_form' => $this->createForm(ConfirmActionType::class, null, ['with_deny' => false, 'allow_label' => 'Renvoyer le code']),
        ]);
    }

    #[Route(path: '/adhesion/nouveau-code', name: 'app_adhesion_request_new_activation_code', methods: ['POST'])]
    public function requestNewCodeAction(Request $request): Response
    {
        $utmParams = UtmParams::fromRequest($request);

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $form = $this->createForm(ConfirmActionType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(new GenerateActivationCodeCommand($adherent));
                $this->addFlash('success', 'Un nouveau code vous a été envoyé par email.');
            } catch (HandlerFailedException $e) {
                if ($exceptions = $e->getWrappedExceptions(ActivationCodeExceptionInterface::class)) {
                    $this->addFlash('error', $exceptions[0]->getMessage());
                } else {
                    throw $e;
                }
            }
        }

        return $this->redirectToRoute(self::ROUTE_NAME, $utmParams);
    }
}
