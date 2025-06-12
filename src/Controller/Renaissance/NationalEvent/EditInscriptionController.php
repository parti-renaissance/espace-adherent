<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Event\Request\EventInscriptionRequest;
use App\Form\NationalEvent\EventInscriptionType;
use App\NationalEvent\EventInscriptionHandler;
use App\NationalEvent\InscriptionStatusEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/{uuid}/{token}', name: 'app_national_event_edit_inscription', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class EditInscriptionController extends AbstractController
{
    public function __construct(
        private readonly EventInscriptionHandler $eventInscriptionHandler,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly string $friendlyCaptchaEuropeSiteKey,
        private readonly string $secret,
    ) {
    }

    public function __invoke(Request $request, EventInscription $inscription, string $token): Response
    {
        if (!hash_equals(hash_hmac('sha256', $inscription->getUuid()->toString(), $this->secret), $token)) {
            throw $this->createNotFoundException();
        }

        $event = $inscription->event;

        if (!$event->allowEditInscription()) {
            $this->addFlash('error', 'L\'édition de votre inscription n\'est plus autorisée.');

            return $this->redirectToRoute('app_national_event_by_slug', ['slug' => $event->getSlug(), 'app_domain' => $request->attributes->get('app_domain')]);
        }

        $inscriptionRequest = EventInscriptionRequest::fromInscription($inscription);

        $inscriptionRequest->setRecaptchaSiteKey($this->friendlyCaptchaEuropeSiteKey);
        $inscriptionRequest->setRecaptcha($request->request->get('frc-captcha-solution'));

        $form = $this
            ->createForm(EventInscriptionType::class, $inscriptionRequest, ['adherent' => $this->getUser()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $inscription->status = InscriptionStatusEnum::PENDING;

            $this->eventInscriptionHandler->handle($event, $inscriptionRequest, $inscription);

            $this->addFlash('success', 'Votre inscription a bien été mise à jour.');

            return $this->redirectToRoute('app_national_event_edit_inscription', ['uuid' => $inscription->getUuid(), 'token' => $token, 'app_domain' => $request->attributes->get('app_domain')]);
        }

        return $this->render('renaissance/national_event/event_inscription_type_'.$event->type->value.'.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'is_open' => true,
            'is_edit' => true,
        ]);
    }
}
