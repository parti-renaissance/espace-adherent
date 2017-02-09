<?php

namespace AppBundle\Controller;

use AppBundle\Committee\CommitteeCreationCommand;
use AppBundle\Form\AdherentChangePasswordType;
use AppBundle\Form\AdherentEmailSubscriptionType;
use AppBundle\Form\AdherentInterestsFormType;
use AppBundle\Form\CreateCommitteeCommandType;
use AppBundle\Form\UpdateMembershipRequestType;
use AppBundle\Membership\MembershipRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-adherent")
 */
class AdherentController extends Controller
{
    /**
     * @Route("/mon-profil", name="app_adherent_profile")
     * @Method("GET|POST")
     */
    public function profileAction(Request $request): Response
    {
        $adherent = $this->getUser();
        $membership = MembershipRequest::createFromAdherent($adherent);
        $form = $this->createForm(UpdateMembershipRequestType::class, $membership)
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.membership_request_handler')->update($adherent, $membership);
            $this->addFlash('info', $this->get('translator')->trans('adherent.update_profile.success'));

            return $this->redirectToRoute('app_adherent_profile');
        }

        return $this->render('adherent/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to pin his/her interests.
     *
     * @Route("/mon-profil/centres-d-interet", name="app_adherent_pin_interests")
     * @Method("GET|POST")
     */
    public function pinInterestsAction(Request $request): Response
    {
        $form = $this->createForm(AdherentInterestsFormType::class, $this->getUser())
            ->add('submit', SubmitType::class, ['label' => 'Enregistrer les modifications'])
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', $this->get('translator')->trans('adherent.update_interests.success'));

            return $this->redirectToRoute('app_adherent_pin_interests');
        }

        return $this->render('adherent/pin_interests.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to change his/her current password.
     *
     * @Route("/mon-profil/changer-mot-de-passe", name="app_adherent_change_password")
     * @Method("GET|POST")
     */
    public function changePasswordAction(Request $request): Response
    {
        $form = $this->createForm(AdherentChangePasswordType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('app.adherent_change_password_handler')->changePassword($this->getUser(), $form->get('password')->getData());
            $this->addFlash('info', $this->get('translator')->trans('adherent.update_password.success'));

            return $this->redirectToRoute('app_adherent_change_password');
        }

        return $this->render('adherent/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to choose his/her email notifications.
     *
     * @Route("/mon-profil/preferences-des-emails", name="app_adherent_set_email_notifications")
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

            return $this->redirectToRoute('app_adherent_set_email_notifications');
        }

        return $this->render('adherent/set_email_notifications.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to create a committee.
     *
     * @Route("/creer-mon-comite", name="app_adherent_create_committee")
     * @Method("GET|POST")
     * @Security("is_granted('CREATE_COMMITTEE')")
     */
    public function createCommitteeAction(Request $request): Response
    {
        $command = CommitteeCreationCommand::createFromAdherent($user = $this->getUser());
        $form = $this->createForm(CreateCommitteeCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.committee_creation_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.creation.success'));

            return $this->redirect($this->get('app.committee.url_generator')->getPath('app_committee_show', $command->getCommittee()));
        }

        return $this->render('adherent/create_committee.html.twig', [
            'form' => $form->createView(),
            'adherent' => $user,
        ]);
    }

    public function listMyCommitteesAction(): Response
    {
        $manager = $this->get('app.committee_manager');

        return $this->render('adherent/list_my_committees.html.twig', [
            'committees' => $manager->getAdherentCommittees($this->getUser()),
        ]);
    }
}
