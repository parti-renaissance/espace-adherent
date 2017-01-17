<?php

namespace AppBundle\Controller\Security;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Exception\AdherentTokenExpiredException;
use AppBundle\Form\AdherentResetPasswordType;
use AppBundle\Form\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Route("/espace-adherent")
 */
class AdherentSecurityController extends Controller
{
    /**
     * @Route("/connexion", name="app_adherent_login")
     * @Method("GET")
     */
    public function loginAction(): Response
    {
        $securityUtils = $this->get('security.authentication_utils');

        $form = $this->get('form.factory')->createNamed('', LoginType::class, [
            '_adherent_email' => $securityUtils->getLastUsername(),
        ]);

        return $this->render('security/adherent_login.html.twig', [
            'form' => $form->createView(),
            'error' => $securityUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/connexion/check", name="app_adherent_login_check")
     * @Method("POST")
     */
    public function loginCheck()
    {
    }

    /**
     * @Route("/deconnexion", name="app_adherent_logout")
     * @Method("GET")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/mot-de-passe-oublie", name="adherent_forgot_password")
     * @Method("GET|POST")
     */
    public function retrieveForgotPasswordAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, ['constraints' => new NotBlank()])
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            if ($adherent = $this->getDoctrine()->getRepository(Adherent::class)->findByEmail($email)) {
                $this->get('app.adherent_reset_password_handler')->handle($adherent);
            }

            $this->addFlash('info', $this->get('translator')->trans('adherent.reset_password.email_sent'));
        }

        return $this->render('security/adherent_forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *   path="/changer-mot-de-passe/{adherent_uuid}/{reset_password_token}",
     *   name="adherent_reset_password",
     *   requirements={
     *     "adherent_uuid": "%pattern_uuid%",
     *     "reset_password_token": "%pattern_sha1%"
     *   }
     * )
     * @Method("GET|POST")
     * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
     * @Entity("resetPasswordToken", expr="repository.findByToken(reset_password_token)")
     */
    public function resetPasswordAction(Request $request, Adherent $adherent, AdherentResetPasswordToken $resetPasswordToken)
    {
        if ($resetPasswordToken->getUsageDate()) {
            throw $this->createNotFoundException('No available reset password token.');
        }

        $form = $this->createForm(AdherentResetPasswordType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();
            try {
                $this->get('app.adherent_reset_password_handler')->reset($adherent, $resetPasswordToken, $newPassword);
                $this->addFlash('info', $this->get('translator')->trans('adherent.reset_password.success'));

                return $this->redirectToRoute('app_adherent_profile');
            } catch (AdherentTokenExpiredException $e) {
                $this->addFlash('info', $this->get('translator')->trans('adherent.reset_password.expired_key'));
            }
        }

        return $this->render('security/adherent_reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
