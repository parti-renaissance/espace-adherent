<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Controller\EnMarche\DonationController;
use App\Donation\PayboxFormFactory;
use App\Donation\TransactionCallbackHandler;
use App\Entity\Donation;
use App\Form\Renaissance\Adhesion\AdditionalInfoType;
use App\Security\AuthenticationUtils;
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
        AuthenticationUtils $authenticationUtils,
        string $status
    ): Response {
        $retryUrl = null;
        $successful = DonationController::RESULT_STATUS_EFFECTUE === $status;

        if (!$successful) {
            $retryUrl = $this->generateUrl('app_renaissance_adhesion_payment', ['uuid' => $donation->getUuid()]);
        }

        if ($successful) {
            $authenticationUtils->authenticateAdherent($donation->getDonator()->getAdherent());
        }

        return $this->render('renaissance/adhesion/result.html.twig', [
            'successful' => $successful,
            'result_code' => $request->query->get('result'),
            'retry_url' => $retryUrl,
            'additional_info_form' => $this
                ->createForm(
                    AdditionalInfoType::class,
                    $adherent = $donation->getDonator()->getAdherent(),
                    [
                        'action' => $this->generateUrl('app_renaissance_adhesion_additional_informations'),
                        'from_certified_adherent' => $adherent->isCertified(),
                    ]
                )->createView(),
        ]);
    }
}
