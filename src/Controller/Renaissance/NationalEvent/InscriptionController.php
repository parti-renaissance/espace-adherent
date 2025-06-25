<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\Adherent;
use App\Entity\NationalEvent\NationalEvent;
use App\Event\Request\EventInscriptionRequest;
use App\Form\NationalEvent\CampusEventInscriptionType;
use App\Form\NationalEvent\DefaultEventInscriptionType;
use App\NationalEvent\EventInscriptionManager;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use App\Repository\NationalEvent\NationalEventRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\Utils\UtmParams;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/{slug}', name: 'app_national_event_by_slug', requirements: ['slug' => '[^/]+'], methods: ['GET', 'POST'])]
#[Route('/{slug}/{pid}', name: 'app_national_event_by_slug_with_referrer', requirements: ['slug' => '[^/]+', 'pid' => '%pattern_pid%'], methods: ['GET', 'POST'])]
class InscriptionController extends AbstractController
{
    private const SESSION_ID = 'nation_event:sess_id';

    public function __construct(
        private readonly NationalEventRepository $nationalEventRepository,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EventInscriptionManager $eventInscriptionManager,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly AnonymousFollowerSession $anonymousFollowerSession,
        private readonly string $friendlyCaptchaEuropeSiteKey,
    ) {
    }

    public function __invoke(Request $request, string $app_domain, ?NationalEvent $event = null, ?string $pid = null, #[CurrentUser] ?Adherent $user = null): Response
    {
        if (!$event && !$event = $this->nationalEventRepository->findOneForInscriptions()) {
            return $this->redirectToRoute('renaissance_site');
        }

        if ($response = $this->anonymousFollowerSession->start($request)) {
            return $response;
        }

        $session = $request->getSession();

        if (!$sessionId = (string) $session->get(self::SESSION_ID)) {
            $session->set(self::SESSION_ID, $sessionId = Uuid::uuid4()->toString());
        }

        $inscriptionRequest = new EventInscriptionRequest($event->getId(), $sessionId, $request->getClientIp(), $event->transportConfiguration);

        if ($user) {
            if ($existingInscription = $this->eventInscriptionRepository->findOneForAdherent($user, $event)) {
                if ($event->isCampus()) {
                    return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $existingInscription->getUuid()->toString(), 'app_domain' => $app_domain]);
                }

                return $this->redirectToRoute('app_national_event_inscription_confirmation', ['slug' => $event->getSlug(), 'app_domain' => $app_domain, 'already-registered' => true]);
            }

            $inscriptionRequest->updateFromAdherent($user);
        }

        $inscriptionRequest->setRecaptchaSiteKey($this->friendlyCaptchaEuropeSiteKey);
        $inscriptionRequest->setRecaptcha($request->request->get('frc-captcha-solution'));

        if ($request->query->has(UtmParams::UTM_SOURCE)) {
            $inscriptionRequest->utmSource = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_SOURCE));
        }
        if ($request->query->has(UtmParams::UTM_CAMPAIGN)) {
            $inscriptionRequest->utmCampaign = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_CAMPAIGN));
        }

        if ($pid) {
            $inscriptionRequest->referrerCode = $pid;
        }

        $isOpen = !$event->isComplete($inscriptionRequest->utmSource);

        $form = $this
            ->createInscriptionForm($event, $inscriptionRequest, $user, $isOpen)
            ->handleRequest($request)
        ;

        if ($isOpen && $form->isSubmitted() && $form->isValid()) {
            $inscription = $this->eventInscriptionManager->saveInscription($event, $inscriptionRequest);

            if (InscriptionStatusEnum::WAITING_PAYMENT === $inscription->status && $inscription->isPaymentRequired()) {
                return $this->redirectToRoute('app_national_event_payment', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid(), 'app_domain' => $app_domain]);
            }

            if ($event->isCampus()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $app_domain, 'confirmation' => true]);
            }

            return $this->redirectToRoute('app_national_event_inscription_confirmation', ['slug' => $event->getSlug(), 'app_domain' => $app_domain]);
        }

        return $this->render('renaissance/national_event/inscription/'.$event->type->value.'.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'is_open' => $isOpen,
        ]);
    }

    protected function createInscriptionForm(NationalEvent $event, EventInscriptionRequest $eventInscriptionRequest, ?Adherent $adherent, bool $isOpen): FormInterface
    {
        $defaultOptions = [
            'adherent' => $adherent,
            'disabled' => !$isOpen,
            'validation_groups' => ['Default', 'inscription_creation'],
        ];

        if ($event->isCampus()) {
            return $this->createForm(CampusEventInscriptionType::class, $eventInscriptionRequest, array_merge($defaultOptions, [
                'transport_configuration' => $event->transportConfiguration,
                'reserved_places' => $this->eventInscriptionManager->countReservedPlaces($event),
                'validation_groups' => ['Default', 'inscription_creation', 'inscription_campus_creation'],
            ]));
        }

        return $this->createForm(DefaultEventInscriptionType::class, $eventInscriptionRequest, $defaultOptions);
    }
}
