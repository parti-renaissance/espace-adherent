<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\Payment\RequestParamsBuilder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/{slug}/{uuid}/paiement', name: 'app_national_event_payment', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class PaymentController extends AbstractController
{
    public function __invoke(
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        RequestParamsBuilder $requestParamsBuilder,
    ): Response {
        if (!$inscription->isPaymentRequired() || InscriptionStatusEnum::WAITING_PAYMENT !== $inscription->status) {
            return $this->redirectToRoute('app_national_event_by_slug', ['slug' => $event->getSlug(), 'app_domain' => $app_domain]);
        }

        return $this->render('renaissance/national_event/payment.html.twig', [
            'params' => $requestParamsBuilder->build(
                $inscription,
                $this->generateUrl('app_national_event_payment_status', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $app_domain], UrlGeneratorInterface::ABSOLUTE_URL),
            ),
        ]);
    }
}
