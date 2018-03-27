<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Unregistration;
use AppBundle\Form\AdherentChangePasswordType;
use AppBundle\Form\AdherentEmailSubscriptionType;
use AppBundle\Form\AdherentType;
use AppBundle\Form\UnregistrationType;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Membership\MembershipRequestHandler;
use AppBundle\Membership\UnregistrationCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/parametres/mon-compte")
 */
class UserController extends Controller
{
    private const UNREGISTER_TOKEN = 'unregister_token';

    /**
     * @Route("", name="app_user_profile")
     * @Method("GET|POST")
     */
    public function profileOverviewAction(): Response
    {
        return $this->render('user/my_account.html.twig');
    }

    /**
     * @Route("/modifier", name="app_user_edit")
     * @Method("GET|POST")
     */
    public function profileAction(Request $request): Response
    {
        $adherent = $this->getUser();
        $membership = MembershipRequest::createFromAdherent($adherent);
        $form = $this->createForm(AdherentType::class, $membership)
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get(MembershipRequestHandler::class)->update($adherent, $membership);
            $this->addFlash('info', $this->get('translator')->trans('adherent.update_profile.success'));

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('adherent/profile.html.twig', ['form' => $form->createView()]);
    }

    /**
     * This action enables an adherent to change his/her current password.
     *
     * @Route("/changer-mot-de-passe", name="app_user_change_password")
     * @Method("GET|POST")
     */
    public function changePasswordAction(Request $request): Response
    {
        $form = $this->createForm(AdherentChangePasswordType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.adherent_change_password_handler')->changePassword($this->getUser(), $form->get('password')->getData());
            $this->addFlash('info', $this->get('translator')->trans('adherent.update_password.success'));

            return $this->redirectToRoute('app_user_change_password');
        }

        return $this->render('adherent/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to choose his/her email notifications.
     *
     * @Route("/preferences-des-emails", name="app_user_set_email_notifications")
     * @Method("GET|POST")
     */
    public function setEmailNotificationsAction(Request $request): Response
    {
        $form = $this->createForm(AdherentEmailSubscriptionType::class, $this->getUser(), ['with_submit_button' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', $this->get('translator')->trans('adherent.set_emails_notifications.success'));

            return $this->redirectToRoute('app_user_set_email_notifications');
        }

        return $this->render('user/set_email_notifications.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/desadherer", name="app_user_terminate_membership")
     * @Method("GET|POST")
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
}
