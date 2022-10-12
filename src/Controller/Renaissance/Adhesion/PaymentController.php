<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Controller\EnMarche\DonationController;
use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Donation\PayboxFormFactory;
use App\Donation\TransactionCallbackHandler;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Form\Renaissance\Adhesion\PrePaymentType;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PaymentController extends AbstractAdhesionController
{
    /**
     * @Route(path="/adhesion/pre-paiement", name="app_renaissance_adhesion_pre_payment", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function prePaymentAction(
        Request $request,
        UserInterface $adherent,
        DonationRequestHandler $donationRequestHandler
    ): Response {
        /** @var Adherent $adherent */
        if ($adherent->getLastMembershipDonation()) {
            return $this->redirectToRoute('app_renaissance_adhesion_additional_informations');
        }

        if ($amount = $request->query->getInt('amount')) {
            $donationRequest = DonationRequest::createFromAdherent($adherent, $request->getClientIp(), $amount);
            $donationRequest->forMembership();

            $donation = $donationRequestHandler->handle($donationRequest, $adherent);

            return $this->redirectToRoute('app_renaissance_adhesion_payment', [
                'uuid' => $donation->getUuid(),
            ]);
        }

        return $this->render('renaissance/adhesion/pre-payment.html.twig', [
            'form' => $this->createForm(PrePaymentType::class)->createView(),
        ]);
    }

    /**
     * @Route(path="/adhesion/{uuid}/paiement", requirements={"uuid": "%pattern_uuid%"}, name="app_renaissance_adhesion_payment", methods={"GET"})
     */
    public function paymentAction(PayboxFormFactory $payboxFormFactory, Donation $donation): Response
    {
        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation);

        return $this->render('renaissance/adhesion/payment.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route(path="/adhesion/callback/{_callback_token}", name="app_renaissance_adhesion_callback", methods={"GET"})
     */
    public function callbackAction(
        Request $request,
        TransactionCallbackHandler $transactionCallbackHandler,
        string $_callback_token
    ): Response {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('app_renaissance_adhesion');
        }

        return $transactionCallbackHandler->handle($id, $request, $_callback_token, true);
    }

    /**
     * @Route(
     *     path="/adhesion/{uuid}/{status}",
     *     requirements={"status": "effectue|erreur", "uuid": "%pattern_uuid%"},
     *     name="app_renaissance_adhesion_payment_result",
     *     methods={"GET"}
     * )
     * @ParamConverter("donation", options={"mapping": {"uuid": "uuid"}})
     */
    public function resultAction(Request $request, Donation $donation, string $status): Response
    {
        if (DonationController::RESULT_STATUS_EFFECTUE === $status) {
            return $this->redirectToRoute('app_renaissance_adhesion_additional_informations', ['from_payment' => true]);
        }

        return $this->render('renaissance/adhesion/result.html.twig', [
            'result_code' => $request->query->get('result'),
            'retry_url' => $this->generateUrl('app_renaissance_adhesion_payment', ['uuid' => $donation->getUuid()]),
        ]);
    }
}
