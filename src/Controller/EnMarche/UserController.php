<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Form\AdherentEmailSubscriptionType;
use AppBundle\Form\MembershipRequestType;
use AppBundle\Form\UnregistrationType;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Membership\UnregistrationCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/parametres/mon-compte")
 */
class UserController extends Controller implements NeedSyncUserInterface
{
    const UNREGISTER_TOKEN = 'unregister_token';

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
        $form = $this->createForm(MembershipRequestType::class, $membership)
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.membership_request_handler')->update($adherent, $membership);
            $this->addFlash('info', $this->get('translator')->trans('adherent.update_profile.success'));

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('adherent/profile.html.twig', [
            'form' => $form->createView(),
            'authProfileUpdateUrl' => $this->generateUrl('auth_user_profile', [
                'redirect_uri' => $this->generateUrl('app_user_edit', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'client_id' => $this->getParameter('auth_client_id'),
            ]),
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
        $form = $this->createForm(AdherentEmailSubscriptionType::class, $this->getUser())
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;

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

        $form = $this->createForm(UnregistrationType::class, $unregistrationCommand, [
            'csrf_token_id' => self::UNREGISTER_TOKEN,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.membership_request_handler')->terminateMembership($unregistrationCommand, $adherent);

            return $this->render('adherent/terminate_membership.html.twig', [
                'unregistered' => true,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('adherent/terminate_membership.html.twig', [
            'unregistered' => false,
            'form' => $form->createView(),
        ]);
    }
}
