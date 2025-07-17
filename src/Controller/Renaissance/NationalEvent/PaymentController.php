<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\NationalEvent\Payment\RequestParamsBuilder;
use App\NationalEvent\PaymentStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    #[Route('/{slug}/{uuid}/paiement', name: 'app_national_event_new_payment', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
    public function newPaymentAction(
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        RequestParamsBuilder $requestParamsBuilder,
        EntityManagerInterface $entityManager,
        RateLimiterFactory $paymentRetryLimiter,
    ): Response {
        if (!$inscription->isPaymentRequired() || PaymentStatusEnum::CONFIRMED === $inscription->paymentStatus) {
            if ($event->isCampus()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $app_domain]);
            }

            return $this->redirectToRoute('app_national_event_by_slug', ['slug' => $event->getSlug(), 'app_domain' => $app_domain]);
        }

        $limiter = $paymentRetryLimiter->create('meeting.inscription.'.$inscription->getUuid()->toString());

        if (!$limiter->consume()->isAccepted()) {
            return $this->redirectToRoute('app_national_event_payment_status', [
                'slug' => $event->getSlug(),
                'uuid' => $inscription->getUuid()->toString(),
                'app_domain' => $app_domain,
                'status' => 'limit',
            ]);
        }

        $paymentParams = $requestParamsBuilder->build(
            $uuid = Uuid::uuid4(),
            $inscription->amount,
            $inscription,
            $this->generateUrl('app_national_event_payment_status', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $app_domain], UrlGeneratorInterface::ABSOLUTE_URL),
        );

        $inscription->addPayment(new Payment(
            $uuid,
            $inscription,
            $inscription->amount,
            $inscription->visitDay,
            $inscription->transport,
            $inscription->accommodation,
            $inscription->withDiscount,
            $paymentParams
        ));

        $entityManager->flush();

        return $this->redirectToRoute('app_national_event_payment', [
            'slug' => $event->getSlug(),
            'uuid' => $uuid->toString(),
            'app_domain' => $app_domain,
        ]);
    }

    #[Route('/{slug}/{uuid}/paiement-process', name: 'app_national_event_payment', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
    public function paymentAction(
        Request $request,
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Payment $payment,
    ): Response {
        if (!$payment->isPending()) {
            $this->addFlash('error', 'Ce paiement n\'est pas valide ou a déjà été traité.');

            return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $payment->inscription->getUuid()->toString(), 'app_domain' => $app_domain]);
        }

        $inscription = $payment->inscription;

        if ($inscription->isRejectedState()) {
            return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toString(), 'app_domain' => $app_domain]);
        }

        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('submit', SubmitType::class, ['label' => 'Continuer vers ma banque'])
            ->getForm()
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->render('renaissance/national_event/payment.html.twig', [
                'params' => $payment->payload,
            ]);
        }

        return $this->render('renaissance/national_event/pre-payment.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
