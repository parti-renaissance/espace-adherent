<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Donation\Paybox\PayboxFormFactory;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\NationalEvent\EventInscriptionManager;
use App\NationalEvent\Payment\Paybox\InscriptionDonationFactory;
use App\NationalEvent\Payment\Worldline\CheckoutInitiator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    private const PSP_PAYBOX = 'paybox';

    #[Route('/{slug}/{uuid}/paiement', name: 'app_national_event_new_payment', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
    public function newPaymentAction(
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        EventInscriptionManager $eventInscriptionManager,
        EntityManagerInterface $entityManager,
        RateLimiterFactory $paymentRetryLimiter,
    ): Response {
        if (!$inscription->isPaymentRequired() || $inscription->hasConfirmedPaymentForCurrentPackage()) {
            if ($event->isPackageEventType()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toRfc4122(), 'app_domain' => $app_domain]);
            }

            return $this->redirectToRoute('app_national_event_by_slug', ['slug' => $event->getSlug(), 'app_domain' => $app_domain]);
        }

        $limiter = $paymentRetryLimiter->create('meeting.inscription.'.$inscription->getUuid()->toRfc4122());

        if (!$limiter->consume()->isAccepted()) {
            return $this->redirectToRoute('app_national_event_payment_status', [
                'slug' => $event->getSlug(),
                'uuid' => $inscription->getUuid()->toRfc4122(),
                'app_domain' => $app_domain,
                'status' => 'limit',
            ]);
        }

        $payment = $eventInscriptionManager->createPayment($inscription);

        $entityManager->flush();

        return $this->redirectToRoute('app_national_event_payment', [
            'slug' => $event->getSlug(),
            'uuid' => $payment->getUuid()->toRfc4122(),
            'app_domain' => $app_domain,
        ]);
    }

    #[Route('/{slug}/{uuid}/paiement-process', name: 'app_national_event_payment', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
    public function paymentAction(
        Request $request,
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Payment $payment,
        CheckoutInitiator $checkoutInitiator,
        PayboxFormFactory $payboxFormFactory,
        InscriptionDonationFactory $inscriptionDonationFactory,
        #[Autowire('%national_event_payment_psp%')] string $paymentPsp,
    ): Response {
        if (!$payment->isPending()) {
            $this->addFlash('error', 'Ce paiement n\'est pas valide ou a déjà été traité.');

            return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $payment->inscription->getUuid()->toRfc4122(), 'app_domain' => $app_domain]);
        }

        $inscription = $payment->inscription;

        if ($inscription->isRejectedState() || $inscription->hasConfirmedPaymentForCurrentPackage()) {
            return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toRfc4122(), 'app_domain' => $app_domain]);
        }

        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->add('submit', SubmitType::class, ['label' => 'Continuer vers ma banque'])
            ->getForm()
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            // The rail of a payment already in flight is carried by the payment itself, never by the parameter:
            // flipping the switch must not strand a payment on a rail it never started on.
            $usePaybox = null !== $payment->donation
                || (null === $payment->hostedCheckoutId && self::PSP_PAYBOX === $paymentPsp);

            if ($usePaybox) {
                // Idempotent: coming back to this form must reuse the donation, as Payment::$donation is unique.
                if (null === $payment->donation) {
                    $inscriptionDonationFactory->createForPayment($payment);
                }

                $paybox = $payboxFormFactory->createPayboxFormForDonation($payment->donation, 'app_national_event_payment_callback');

                return $this->render('renaissance/payment/payment.html.twig', [
                    'url' => $paybox->getUrl(),
                    'form' => $paybox->getForm()->createView(),
                ]);
            }

            $returnUrl = $this->generateUrl('app_national_event_payment_status', [
                'slug' => $event->getSlug(),
                'uuid' => $inscription->getUuid()->toRfc4122(),
                'app_domain' => $app_domain,
                'payment' => $payment->getUuid()->toRfc4122(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            return $this->redirect($checkoutInitiator->initiate($payment, $returnUrl));
        }

        return $this->render('renaissance/national_event/pre-payment.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
