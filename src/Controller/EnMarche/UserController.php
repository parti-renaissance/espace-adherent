<?php

namespace App\Controller\EnMarche;

use App\Adherent\Unregistration\UnregistrationCommand;
use App\AdherentCharter\AdherentCharterFactory;
use App\AdherentCharter\AdherentCharterTypeEnum;
use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileHandler;
use App\Entity\Adherent;
use App\Entity\Unregistration;
use App\Form\AdherentChangePasswordType;
use App\Form\AdherentEmailSubscriptionType;
use App\Form\AdherentProfileType;
use App\Form\UnregistrationType;
use App\Membership\AdherentChangePasswordHandler;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipRequestHandler;
use App\Membership\UserEvents;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/parametres/mon-compte")
 */
class UserController extends AbstractController
{
    private const UNREGISTER_TOKEN = 'unregister_token';

    /**
     * @Route("/modifier", name="app_user_edit", methods={"GET", "POST"})
     */
    public function profileAction(Request $request, AdherentProfileHandler $handler): Response
    {
        $adherent = $this->getUser();
        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        $form = $this
            ->createForm(AdherentProfileType::class, $adherentProfile, ['disabled_form' => $adherent->isCertified()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->update($adherent, $adherentProfile);
            $this->addFlash('info', 'adherent.update_profile.success');

            return $this->redirectToRoute('app_user_edit');
        }

        return $this->render('adherent/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * This action enables an adherent to change his/her current password.
     *
     * @Route("/changer-mot-de-passe", name="app_user_change_password", methods={"GET", "POST"})
     */
    public function changePasswordAction(Request $request, AdherentChangePasswordHandler $handler): Response
    {
        $form = $this->createForm(AdherentChangePasswordType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $handler->changePassword($this->getUser(), $form->get('password')->getData());
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
        EventDispatcherInterface $dispatcher,
        SubscriptionHandler $subscriptionHandler
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $oldEmailsSubscriptions = $adherent->getSubscriptionTypes();

        $dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

        $form = $this
            ->createForm(AdherentEmailSubscriptionType::class, $adherent, [
                'is_adherent' => $adherent->isAdherent(),
                'validation_groups' => 'subscriptions_update',
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $subscriptionHandler->handleChanges($adherent, $oldEmailsSubscriptions);

            $this->addFlash('info', 'adherent.set_emails_notifications.success');

            return $this->redirectToRoute('app_user_set_email_notifications');
        }

        return $this->render('user/set_email_notifications.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/desadherer", name="app_user_terminate_membership", methods={"GET", "POST"})
     * @Security("is_granted('UNREGISTER')")
     */
    public function terminateMembershipAction(
        Request $request,
        MembershipRequestHandler $handler,
        TokenStorageInterface $tokenStorage
    ): Response {
        /** @var Adherent $adherent */
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
            $handler->terminateMembership($adherent, $unregistrationCommand, $adherent->isAdherent());
            $tokenStorage->setToken(null);
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
