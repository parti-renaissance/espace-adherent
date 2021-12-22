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
use App\Membership\MembershipNotifier;
use App\OAuth\App\AuthAppUrlManager;
use App\OAuth\App\PlatformAuthUrlGenerator;
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
        $currentApp = $request->attributes->get('app');

        if ($this->getUser()) {
            if ($currentApp) {
                return $this->redirect($this->getParameter($currentApp.'_host'));
            }

            return $this->redirectToRoute('app_search_events');
        }

        $form = $formFactory->createNamed('', LoginType::class, [
            '_login_email' => $securityUtils->getLastUsername(),
        ]);

        return $this->render($currentApp ? sprintf('security/%s_user_login.html.twig', $currentApp) : 'security/adherent_login.html.twig', [
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
        $currentApp = $request->attributes->get('app');

        if ($this->getUser()) {
            if ($currentApp) {
                return $this->redirect($this->getParameter($currentApp.'_host'));
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

            if (($adherent = $adherentRepository->findOneByEmail($email)) && $currentApp === $adherent->getSource()) {
                $handler->handle($adherent);
            }

            $this->addFlash('info', 'adherent.reset_password.email_sent');

            if ($currentApp) {
                return $this->redirectToRoute(sprintf('app_%s_login', $currentApp));
            }

            return $this->redirectToRoute('app_user_login');
        }

        return $this->render($currentApp ? sprintf('security/%s_forgot_password.html.twig', $currentApp) : 'security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
     * @Entity("resetPasswordToken", expr="repository.findByToken(reset_password_token)")
     */
    public function resetPasswordAction(
        Request $request,
        Adherent $adherent,
        AdherentResetPasswordToken $resetPasswordToken,
        AdherentResetPasswordHandler $handler,
        AuthAppUrlManager $appUrlManager
    ): Response {
        $appUrlGenerator = $appUrlManager->getUrlGenerator($appUrlManager->getAppCodeFromRequest($request) ?? PlatformAuthUrlGenerator::getAppCode());

        if ($this->getUser()) {
            return $this->redirect($appUrlGenerator->generateHomepageLink());
        }

        if ($resetPasswordToken->getUsageDate()) {
            throw $this->createNotFoundException('No available reset password token.');
        }

        $form = $this
            ->createForm(AdherentResetPasswordType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();

            try {
                $handler->reset($adherent, $resetPasswordToken, $newPassword);
                $this->addFlash('info', 'adherent.reset_password.success');

                return $this->redirect($appUrlGenerator->generateSuccessResetPasswordLink($request));
            } catch (AdherentTokenExpiredException $e) {
                $this->addFlash('info', 'adherent.reset_password.expired_key');
            }
        }

        return $this->render(sprintf('security/%s_reset_password.html.twig', $appUrlGenerator::getAppCode()), [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/renvoyer-validation", name="adherent_resend_validation", methods={"GET"})
     */
    public function resendValidationEmailAction(
        MembershipNotifier $membershipNotifier,
        AuthenticationUtils $authenticationUtils,
        AdherentRepository $adherentRepository
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $adherentRepository->loadUserByUsername($authenticationUtils->getLastUsername());

        if ($adherent && !$adherent->isEnabled() && !$adherent->getActivatedAt()) {
            $membershipNotifier->sendEmailValidation($adherent);
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
