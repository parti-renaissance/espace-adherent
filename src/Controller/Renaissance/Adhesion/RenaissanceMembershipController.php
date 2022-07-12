<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Address\GeoCoder;
use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Donation\DonationRequestUtils;
use App\Donation\PayboxFormFactory;
use App\Donation\TransactionCallbackHandler;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\Donation;
use App\Exception\AdherentAlreadyEnabledException;
use App\Exception\AdherentTokenExpiredException;
use App\Form\RenaissanceAdherentRegistrationType;
use App\Membership\AdherentAccountActivationHandler;
use App\Membership\MembershipNotifier;
use App\Membership\MembershipRegistrationProcess;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Membership\MembershipRequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/adhesion")
 */
class RenaissanceMembershipController extends AbstractController
{
    public const RESULT_STATUS_EFFECTUE = 'effectue';

    /**
     * @Route(name="app_renaissance_index_action", methods={"GET"})
     */
    public function indexAction(Request $request): Response
    {
        if (!$amount = $request->query->get('montant')) {
            return $this->render('renaissance/adhesion/index.html.twig');
        }

        return $this->redirectToRoute('app_renaissance_information_action', [
            'montant' => $amount,
        ]);
    }

    /**
     * @Route("/coordonnees", name="app_renaissance_information_action", methods={"GET", "POST"})
     */
    public function informationAction(
        Request $request,
        GeoCoder $geoCoder,
        TranslatorInterface $translator,
        MembershipRequestHandler $membershipRequestHandler,
        DonationRequestHandler $donationRequestHandler,
        EntityManagerInterface $entityManager
    ): Response {
        if (!($amount = $request->query->get('montant')) || !is_numeric($amount)) {
            return $this->redirectToRoute('app_renaissance_index_action');
        }

        $membership = RenaissanceMembershipRequest::createWithCaptcha(
            $geoCoder->getCountryCodeFromIp($clientIP = $request->getClientIp()),
            $request->request->get('g-recaptcha-response')
        );
        $membership->setClientIp($clientIP);
        $membership->setAmount(\floatval($amount));

        $form = $this
            ->createForm(RenaissanceAdherentRegistrationType::class, $membership)
            ->add('submit', SubmitType::class, ['label' => 'Continuer'])
            ->handleRequest($request)
        ;

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $adherent = $membershipRequestHandler->createRenaissanceAdherent($membership);

                $donationRequest = DonationRequest::createFromAdherent($adherent, $membership->getClientIp(), $membership->getAmount());
                $donationRequest->forMembership();

                $donationRequestHandler->handle($donationRequest);

                return $this->redirectToRoute('app_renaissance_adhesion_pay', [
                    'uuid' => $donationRequest->getUuid(),
                ]);
            }
        } catch (ExceptionInterface $e) {
            $this->addFlash('error_recaptcha', $translator->trans('recaptcha.error'));
        }

        return $this->render('renaissance/adhesion/information.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{uuid}/paiement", requirements={"uuid": "%pattern_uuid%"}, name="app_renaissance_adhesion_pay", methods={"GET"})
     */
    public function payboxAction(PayboxFormFactory $payboxFormFactory, Donation $donation): Response
    {
        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation, true);

        return $this->render('renaissance/adhesion/paybox.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }

    /**
     * @Route("/callback/{_callback_token}", name="app_renaissance_adhesion_callback", methods={"GET"})
     */
    public function callbackAction(
        Request $request,
        TransactionCallbackHandler $transactionCallbackHandler,
        string $_callback_token
    ): Response {
        $id = explode('_', $request->query->get('id'))[0];

        if (!$id || !Uuid::isValid($id)) {
            return $this->redirectToRoute('app_renaissance_index_action');
        }

        return $transactionCallbackHandler->handle($id, $request, $_callback_token, true);
    }

    /**
     * @Route(
     *     "/{uuid}/{status}",
     *     requirements={"status": "effectue|erreur", "uuid": "%pattern_uuid%"},
     *     name="app_renaissance_adhesion_pay_result",
     *     methods={"GET"}
     * )
     * @ParamConverter("donation", options={"mapping": {"uuid": "uuid"}})
     */
    public function resultAction(
        Request $request,
        Donation $donation,
        DonationRequestUtils $donationRequestUtils,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        MembershipRequestHandler $membershipRequestHandler,
        string $status
    ): Response {
        $retryUrl = null;
        $successful = self::RESULT_STATUS_EFFECTUE === $status;

        if (!$successful) {
            if ($adherent = $donation->getAdherent()) {
                $membershipRequestHandler->removeUnsuccessfulRenaissainceAdhesion($adherent);
            }
            $retryUrl = $this->generateUrl(
                'app_renaissance_index_action'
            );
        }

        $membershipRegistrationProcess->terminate();

        return $this->render('renaissance/adhesion/result.html.twig', [
            'successful' => $successful,
            'result_code' => $request->query->get('result'),
            'donation' => $donation,
            'retry_url' => $retryUrl,
        ]);
    }

    /**
     * This action enables a new user to activate his\her newly created
     * membership account.
     *
     * @Route(
     *     path="/inscription/finaliser/{adherent_uuid}/{activation_token}",
     *     name="app_renaissance_membership_activate",
     *     requirements={
     *         "adherent_uuid": "%pattern_uuid%",
     *         "activation_token": "%pattern_sha1%"
     *     },
     *     methods={"GET"}
     * )
     * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
     * @Entity("activationToken", expr="repository.findByToken(activation_token)")
     */
    public function activateAction(
        Adherent $adherent,
        AdherentActivationToken $activationToken,
        AdherentAccountActivationHandler $accountActivationHandler,
        MembershipNotifier $membershipNotifier
    ): Response {
        try {
            $accountActivationHandler->handle($adherent, $activationToken);

            if ($adherent->isAdherent()) {
                $membershipNotifier->sendConfirmationJoinMessage($adherent);
            }

            return $this->render('renaissance/adhesion/adhesion_complete.html.twig');
        } catch (AdherentAlreadyEnabledException $e) {
            $this->addFlash('info', 'adherent.activation.already_active');
        } catch (AdherentTokenExpiredException $e) {
            $this->addFlash('info', 'adherent.activation.expired_key');
        }

        return $this->redirectToRoute('app_renaissance_index_action');
    }
}
