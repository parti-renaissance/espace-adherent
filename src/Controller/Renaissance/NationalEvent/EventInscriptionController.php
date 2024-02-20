<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Event\Request\EventInscriptionRequest;
use App\Form\NationalEvent\EventInscriptionType;
use App\NationalEvent\EventInscriptionHandler;
use App\Repository\NationalEvent\NationalEventRepository;
use App\Utils\UtmParams;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/', name: 'app_renaissance_national_event_index', methods: ['GET', 'POST'])]
class EventInscriptionController extends AbstractController
{
    private const SESSION_ID = 'nation_event:sess_id';

    public function __construct(
        private readonly NationalEventRepository $nationalEventRepository,
        private readonly EventInscriptionHandler $eventInscriptionHandler,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly string $friendlyCaptchaEuropeSiteKey
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$event = $this->nationalEventRepository->findOneForInscriptions()) {
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
            $inscriptionRequest->utmCampaign = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_CAMPAIGN));
        }

        $form = $this
            ->createForm(EventInscriptionType::class, $inscriptionRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventInscriptionHandler->handle($event, $inscriptionRequest);

            $this->addFlash('success', 'Votre inscription est bien enregistrée');

            return $this->redirectToRoute('app_renaissance_national_event_inscription_confirmation');
        }

        return $this->renderForm('renaissance/national_event/event_inscription.html.twig', [
            'form' => $form,
            'event' => $event,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
        ]);
    }
}
