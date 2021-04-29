<?php

namespace App\Controller\EnMarche\Security;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use App\Entity\AdherentResetPasswordToken;
use App\Exception\AdherentTokenExpiredException;
use App\Form\AdherentResetPasswordType;
use App\Form\LoginType;
use App\Membership\AdherentChangeEmailHandler;
use App\Membership\AdherentResetPasswordHandler;
use App\Membership\MembershipRequestHandler;
use App\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\NotBlank;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="app_user_login", methods={"GET"})
     */
    public function loginAction(
        Request $request,
        AuthenticationUtils $securityUtils,
        FormFactoryInterface $formFactory
    ): Response {
        $coalition = $request->attributes->getBoolean('coalition');

        if ($this->getUser()) {
            if ($coalition) {
                return $this->redirect($this->getParameter('coalitions_host'));
            }

            return $this->redirectToRoute('app_search_events');
        }

        $form = $formFactory->createNamed('', LoginType::class, [
            '_login_email' => $securityUtils->getLastUsername(),
        ]);

        return $this->render($coalition ? 'security/coalition_user_login.html.twig' : 'security/adherent_login.html.twig', [
            'form' => $form->createView(),
            'error' => $securityUtils->getLastAuthenticationError(),
        ]);
    }

    public function loginCheckAction()
    {
    }

    public function logoutAction()
    {
    }

    /**
     * @Route("/mot-de-passe-oublie", name="forgot_password", methods={"GET", "POST"})
     */
    public function retrieveForgotPasswordAction(
        Request $request,
        AdherentResetPasswordHandler $handler,
        AdherentRepository $adherentRepository
    ): Response {
        $coalition = $request->attributes->getBoolean('coalition');

        if ($this->getUser()) {
            if ($coalition) {
                return $this->redirect($this->getParameter('coalitions_host'));
            }

            return $this->redirectToRoute('app_search_events');
        }

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['constraints' => new NotBlank()])
            ->getForm()
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            if ($adherent = $adherentRepository->findOneByEmail($email)) {
                $handler->handle($adherent);
            }

            $this->addFlash('info', 'adherent.reset_password.email_sent');

            if ($coalition) {
                return $this->redirectToRoute('app_coalition_login');
            }

            return $this->redirectToRoute('app_user_login');
        }

        return $this->render($coalition ? 'security/coalition_forgot_password.html.twig' : 'security/forgot_password.html.twig', [
            'legacy' => $request->query->getBoolean('legacy'),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/changer-mot-de-passe/{adherent_uuid}/{reset_password_token}",
     *     name="adherent_reset_password",
     *     requirements={
     *         "adherent_uuid": "%pattern_uuid%",
     *         "reset_password_token": "%pattern_sha1%"
     *     },
     *     methods={"GET", "POST"}
     * )
     * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
     * @Entity("resetPasswordToken", expr="repository.findByToken(reset_password_token)")
     */
    public function resetPasswordAction(
        Request $request,
        Adherent $adherent,
        AdherentResetPasswordToken $resetPasswordToken,
        AdherentResetPasswordHandler $handler
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_search_events');
        }

        if ($resetPasswordToken->getUsageDate()) {
            throw $this->createNotFoundException('No available reset password token.');
        }

        $form = $this->createForm(AdherentResetPasswordType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();

            try {
                $handler->reset($adherent, $resetPasswordToken, $newPassword);
                $this->addFlash('info', 'adherent.reset_password.success');

                return $this->redirectToRoute('app_user_profile');
            } catch (AdherentTokenExpiredException $e) {
                $this->addFlash('info', 'adherent.reset_password.expired_key');
            }
        }

        return $this->render('security/adherent_reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/renvoyer-validation", name="adherent_resend_validation", methods={"GET"})
     */
    public function resendValidationEmailAction(
        MembershipRequestHandler $membershipRequestHandler,
        AuthenticationUtils $authenticationUtils,
        AdherentRepository $adherentRepository
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $adherentRepository->loadUserByUsername($authenticationUtils->getLastUsername());

        if ($adherent && !$adherent->isEnabled() && !$adherent->getActivatedAt()) {
            $membershipRequestHandler->sendEmailValidation($adherent);
            $this->addFlash('success', 'Un email de validation a bien été envoyé.');
        }

        return $this->redirectToRoute('app_user_login');
    }

    /**
     * @Route(
     *     path="/valider-changement-email/{adherent_uuid}/{change_email_token}",
     *     name="user_validate_new_email",
     *     requirements={
     *         "adherent_uuid": "%pattern_uuid%",
     *         "change_email_token": "%pattern_sha1%"
     *     },
     *     methods={"GET"}
     * )
     * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
     * @Entity("token", expr="repository.findByToken(change_email_token)")
     */
    public function activateNewEmailAction(
        Adherent $adherent,
        AdherentChangeEmailToken $token,
        AdherentChangeEmailHandler $handler,
        TokenStorageInterface $tokenStorage
    ): Response {
        if ($token->getUsageDate()) {
            throw $this->createNotFoundException('No available email changing token.');
        }

        try {
            $handler->handleValidationRequest($adherent, $token);

            $this->addFlash('info', 'adherent.change_email.success');
            $tokenStorage->setToken(null);
        } catch (AdherentTokenExpiredException $e) {
            $this->addFlash('info', 'adherent.change_email.expired_key');
        }

        return $this->redirectToRoute('homepage');
    }
}
