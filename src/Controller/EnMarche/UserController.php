<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\AdherentCharter\AdherentCharterFactory;
use AppBundle\AdherentCharter\AdherentCharterTypeEnum;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Unregistration;
use AppBundle\Form\AdherentChangeEmailType;
use AppBundle\Form\AdherentChangePasswordType;
use AppBundle\Form\AdherentEmailSubscriptionType;
use AppBundle\Form\AdherentType;
use AppBundle\Form\UnregistrationType;
use AppBundle\History\EmailSubscriptionHistoryHandler;
use AppBundle\Membership\AdherentChangeEmailHandler;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Membership\MembershipRequestHandler;
use AppBundle\Membership\UnregistrationCommand;
use AppBundle\Membership\UserEvent;
use AppBundle\Membership\UserEvents;
use AppBundle\Repository\DonationRepository;
use AppBundle\Repository\SubscriptionTypeRepository;
use AppBundle\Repository\TransactionRepository;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/parametres/mon-compte")
 */
class UserController extends Controller
{
    private const UNREGISTER_TOKEN = 'unregister_token';

    /**
     * @Route("", name="app_user_profile", methods={"GET"})
     */
    public function profileOverviewAction(): Response
    {
        return $this->render('user/my_account.html.twig');
    }

    /**
     * @Route("/mes-dons", name="app_user_profile_donation", methods={"GET"})
     */
    public function profileDonationAction(
        DonationRepository $donationRepository,
        TransactionRepository $transactionRepository
    ): Response {
        $userEmail = $this->getUser()->getEmailAddress();

        return $this->render('user/my_donation.html.twig', [
            'successful_transactions' => $transactionRepository->findAllSuccessfulTransactionByEmail($userEmail),
            'subscribed_donations' => $donationRepository->findAllSubscribedDonationByEmail($userEmail),
            'last_subscription_ended' => $donationRepository->findLastSubscriptionEndedDonationByEmail($userEmail),
        ]);
    }

    /**
     * @Route("/modifier", name="app_user_edit", methods={"GET", "POST"})
     */
    public function profileAction(Request $request): Response
    {
        $adherent = $this->getUser();
        $membership = MembershipRequest::createFromAdherent($adherent);
        $form = $this->createForm(AdherentType::class, $membership)
            ->remove('emailAddress')
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get(MembershipRequestHandler::class)->update($adherent, $membership);
            $this->addFlash('info', 'adherent.update_profile.success');

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('adherent/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/modifier-email", name="app_user_change_email", methods={"POST", "GET"})
     */
    public function changeEmailAction(Request $request, AdherentChangeEmailHandler $handler): Response
    {
        $form = $this
            ->createForm(AdherentChangeEmailType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handleRequest($this->getUser(), $form->getData()['email']);

            return $this->redirectToRoute('app_user_edit');
        }

        return $this->render('adherent/profile-email.html.twig', ['form' => $form->createView()]);
    }

    /**
     * This action enables an adherent to change his/her current password.
     *
     * @Route("/changer-mot-de-passe", name="app_user_change_password", methods={"GET", "POST"})
     */
    public function changePasswordAction(Request $request): Response
    {
        $form = $this->createForm(AdherentChangePasswordType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.adherent_change_password_handler')->changePassword($this->getUser(), $form->get('password')->getData());
            $this->addFlash('info', 'adherent.update_password.success');

            return $this->redirectToRoute('app_user_change_password');
        }

        return $this->render('adherent/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to choose his/her email notifications.
     *
     * @Route("/preferences-des-emails", name="app_user_set_email_notifications", methods={"GET", "POST"})
     */
    public function setEmailNotificationsAction(
        Request $request,
        EmailSubscriptionHistoryHandler $historyManager,
        SubscriptionTypeRepository $subscriptionTypeRepository,
        EventDispatcher $dispatcher
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $oldEmailsSubscriptions = $adherent->getSubscriptionTypes();

        $dispatcher->dispatch(UserEvents::USER_BEFORE_UPDATE, new UserEvent($adherent));

        $form = $this
            ->createForm(AdherentEmailSubscriptionType::class, $adherent, ['is_adherent' => $adherent->isAdherent()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($adherent->isEmailUnsubscribed() && array_diff($adherent->getSubscriptionTypes(), $oldEmailsSubscriptions)) {
                $adherent->setEmailUnsubscribed(false);
            }

            $historyManager->handleSubscriptionsUpdate($adherent, $oldEmailsSubscriptions);
            $dispatcher->dispatch(UserEvents::USER_UPDATE_SUBSCRIPTIONS, new UserEvent($adherent, null, null, $oldEmailsSubscriptions));

            $this->addFlash('info', 'adherent.set_emails_notifications.success');

            return $this->redirectToRoute('app_user_set_email_notifications');
        }

        return $this->render('user/set_email_notifications.html.twig', [
            'form' => $form->createView(),
            'cpSubscriptionType' => $subscriptionTypeRepository->findOneByCode(SubscriptionTypeEnum::CITIZEN_PROJECT_CREATION_EMAIL),
        ]);
    }

    /**
     * @Route("/desadherer", name="app_user_terminate_membership", methods={"GET", "POST"})
     * @Security("is_granted('UNREGISTER')")
     */
    public function terminateMembershipAction(Request $request): Response
    {
        $adherent = $this->getUser();
        $unregistrationCommand = new UnregistrationCommand();
        $viewFolder = $adherent->isUser() ? 'user' : 'adherent';
        $reasons = $adherent->isUser() ? Unregistration::REASONS_LIST_USER : Unregistration::REASONS_LIST_ADHERENT;

        $form = $this->createForm(UnregistrationType::class, $unregistrationCommand, [
            'csrf_token_id' => self::UNREGISTER_TOKEN,
            'reasons' => array_combine($reasons, $reasons),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(MembershipRequestHandler::class)->terminateMembership($unregistrationCommand, $adherent);
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();

            return $this->render(sprintf('%s/terminate_membership.html.twig', $viewFolder), [
                'unregistered' => true,
                'form' => $form->createView(),
            ]);
        }

        return $this->render(sprintf('%s/terminate_membership.html.twig', $viewFolder), [
            'unregistered' => false,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/chart",
     *     name="app_user_set_accept_chart",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"PUT"}
     * )
     */
    public function chartAcceptationAction(
        Request $request,
        ObjectManager $manager,
        UserInterface $adherent
    ): JsonResponse {
        $charterType = $request->request->all()['charterType'] ?? null;

        if (!AdherentCharterTypeEnum::isValid($charterType)) {
            throw new BadRequestHttpException('Invalid charter type');
        }

        /** @var Adherent $adherent */
        if (!$adherent->getCharters()->hasCharterAcceptedForType($charterType)) {
            $adherent->addCharter(AdherentCharterFactory::create($charterType));
            $manager->flush();
        }

        return new JsonResponse('OK', Response::HTTP_OK);
    }
}
