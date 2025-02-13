<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use App\Event\Request\EventInscriptionRequest;
use App\Form\NationalEvent\EventInscriptionType;
use App\NationalEvent\EventInscriptionHandler;
use App\Repository\NationalEvent\NationalEventRepository;
use App\Utils\UtmParams;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/{slug}', name: 'app_national_event_by_slug', methods: ['GET', 'POST'])]
class EventInscriptionController extends AbstractController
{
    private const SESSION_ID = 'nation_event:sess_id';

    public function __construct(
        private readonly NationalEventRepository $nationalEventRepository,
        private readonly EventInscriptionHandler $eventInscriptionHandler,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly string $friendlyCaptchaEuropeSiteKey,
    ) {
    }

    public function __invoke(Request $request, ?NationalEvent $event = null): Response
    {
        if (!$event && !$event = $this->nationalEventRepository->findOneForInscriptions()) {
            return $this->redirectToRoute('renaissance_site');
        }

        $session = $request->getSession();

        if (!$sessionId = (string) $session->get(self::SESSION_ID)) {
            $session->set(self::SESSION_ID, $sessionId = Uuid::uuid4()->toString());
        }

        $inscriptionRequest = new EventInscriptionRequest($sessionId, $request->getClientIp());
        $inscriptionRequest->setRecaptchaSiteKey($this->friendlyCaptchaEuropeSiteKey);
        $inscriptionRequest->setRecaptcha($request->request->get('frc-captcha-solution'));

        if ($request->query->has(UtmParams::UTM_SOURCE)) {
            $inscriptionRequest->utmSource = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_SOURCE));
        }
        if ($request->query->has(UtmParams::UTM_CAMPAIGN)) {
            $inscriptionRequest->utmCampaign = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_CAMPAIGN));
        }

        $isOpen = !$event->isComplete($inscriptionRequest->utmSource);

        $form = $this
            ->createForm(EventInscriptionType::class, $inscriptionRequest)
            ->handleRequest($request)
        ;

        if ($isOpen && $form->isSubmitted() && $form->isValid()) {
            $this->eventInscriptionHandler->handle($event, $inscriptionRequest);

            $this->addFlash('success', 'Votre inscription est bien enregistrée');

            return $this->redirectToRoute('app_national_event_inscription_confirmation', ['slug' => $event->getSlug()]);
        }

        return $this->render('renaissance/national_event/event_inscription.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'is_open' => $isOpen,
        ]);
    }
}
