<?php

namespace App\Controller\EnMarche\Security;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\Administrator;
use App\Exception\AdherentTokenExpiredException;
use App\Form\AdherentResetPasswordType;
use App\Form\LoginType;
use App\Membership\AdherentChangeEmailHandler;
use App\Membership\AdherentResetPasswordHandler;
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
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_user_login', methods: ['GET'])]
    public function loginAction(
        AuthenticationUtils $securityUtils,
        FormFactoryInterface $formFactory,
        AuthAppUrlManager $appUrlManager,
        ?string $app = null
    ): Response {
        if ($user = $this->getUser()) {
            if ($user instanceof Administrator) {
                return $this->redirectToRoute('admin_app_adherent_list');
            }

            $this->addFlash('info', 'Vous êtes déjà connecté(e)');

            if ($app) {
                return $this->redirect($appUrlManager->getUrlGenerator($app)->generateForLoginSuccess($user));
            }

            return $this->redirectToRoute('app_search_events');
        }

        $form = $formFactory->createNamed('', LoginType::class, [
            '_login_email' => $securityUtils->getLastUsername(),
        ], ['remember_me' => true]);

        return $this->render($app ? \sprintf('security/%s_user_login.html.twig', $app) : 'security/adherent_login.html.twig', [
            'form' => $form->createView(),
            'error' => $securityUtils->getLastAuthenticationError(),
        ]);
    }

    public function loginCheckAction()
    {
    }

    public function retrieveForgotPasswordAction(
        Request $request,
        AdherentResetPasswordHandler $handler,
        AdherentRepository $adherentRepository,
        TranslatorInterface $translatable,
        AuthAppUrlManager $appUrlManager,
    ): Response {
        $currentApp = $appUrlManager->getAppCodeFromRequest($request);
        $urlGenerator = $appUrlManager->getUrlGenerator($currentApp);

        if ($user = $this->getUser()) {
            if ($user instanceof Administrator) {
                return $this->redirectToRoute('admin_app_adherent_list');
            }

            return $this->redirect($urlGenerator->generateForLoginSuccess($user));
        }

        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['required' => true, 'constraints' => new NotBlank()])
            ->getForm()
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            if ($adherent = $adherentRepository->findOneByEmail($email)) {
                $handler->handle($adherent, $currentApp);
            }

            $this->addFlash('info', $translatable->trans('adherent.reset_password.email_sent', ['%email%' => $email]));

            if ($currentApp) {
                return $this->redirectToRoute('app_forgot_password', ['app_domain' => $urlGenerator->getAppHost()]);
            }

            return $this->redirectToRoute('app_user_login');
        }

        return $this->render($currentApp ? \sprintf('security/%s_forgot_password.html.twig', $currentApp) : 'security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Entity('adherent', expr: 'repository.findOneByUuid(adherent_uuid)')]
    #[Entity('resetPasswordToken', expr: 'repository.findByToken(reset_password_token)')]
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
                $handler->reset(
                    $adherent,
                    $resetPasswordToken,
                    $newPassword,
                    $appUrlGenerator::getAppCode(),
                    $request->query->has('is_creation')
                );
                $this->addFlash('info', 'adherent.reset_password.success');

                return $this->redirect($appUrlGenerator->generateSuccessResetPasswordLink($request));
            } catch (AdherentTokenExpiredException $e) {
                $this->addFlash('info', 'adherent.reset_password.expired_key');
            }
        }

        return $this->render(\sprintf('security/%s_reset_password.html.twig', $appUrlGenerator::getAppCode()), [
            'form' => $form->createView(),
        ]);
    }

    #[Entity('adherent', expr: 'repository.findOneByUuid(adherent_uuid)')]
    #[Entity('token', expr: 'repository.findByToken(change_email_token)')]
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

        return $this->redirectToRoute('app_renaissance_adherent_space');
    }

    public function logoutAction()
    {
    }
}
