<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Controller\EnMarche\DonationController;
use App\Donation\PayboxFormFactory;
use App\Donation\TransactionCallbackHandler;
use App\Entity\Donation;
use App\Membership\MembershipRegistrationProcess;
use App\Membership\MembershipRequestHandler;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractAdhesionController
{
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
    public function resultAction(
        Request $request,
        Donation $donation,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        MembershipRequestHandler $membershipRequestHandler,
        string $status
    ): Response {
        $retryUrl = null;
        $successful = DonationController::RESULT_STATUS_EFFECTUE === $status;

        if (!$successful) {
            if (!$this->getUser() && $adherent = $donation->getDonator()->getAdherent()) {
                $membershipRequestHandler->removeUnsuccessfulRenaissanceAdhesion($adherent);
            }
            $retryUrl = $this->generateUrl('app_renaissance_adhesion');
        }

        $membershipRegistrationProcess->terminate();

        return $this->render('renaissance/adhesion/result.html.twig', [
            'successful' => $successful,
            'result_code' => $request->query->get('result'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
        ]);
    }
}
