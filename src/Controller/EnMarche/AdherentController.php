<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Committee\CommitteeCreationCommand;
use AppBundle\Contact\ContactMessage;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\EventRegistrationException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\AdherentChangePasswordType;
use AppBundle\Form\AdherentEmailSubscriptionType;
use AppBundle\Form\AdherentInterestsFormType;
use AppBundle\Form\ContactMessageType;
use AppBundle\Form\CreateCommitteeCommandType;
use AppBundle\Form\UnregistrationType;
use AppBundle\Form\UpdateMembershipRequestType;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Membership\UnregistrationCommand;
use GuzzleHttp\Exception\ConnectException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/espace-adherent")
 */
class AdherentController extends Controller
{
    use CanaryControllerTrait;

    const UNREGISTER_TOKEN = 'unregister_token';

    /**
     * @Route("/mon-compte", name="app_adherent_profile")
     * @Method("GET|POST")
     */
    public function profileOverviewAction()
    {
        return $this->render('adherent/overview.html.twig');
    }

    /**
     * @Route("/mon-compte/modifier", name="app_adherent_edit")
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
     * @Route("/mon-compte/centres-d-interet", name="app_adherent_pin_interests")
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
     * @Route("/mon-compte/changer-mot-de-passe", name="app_adherent_change_password")
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
     * @Route("/mon-compte/preferences-des-emails", name="app_adherent_set_email_notifications")
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
            $this->get('app.committee.creation_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.creation.success'));

            return $this->redirect($this->generateUrl('app_committee_show', ['slug' => $command->getCommittee()->getSlug()]));
        }

        return $this->render('adherent/create_committee.html.twig', [
            'form' => $form->createView(),
            'adherent' => $user,
        ]);
    }

    /**
     * @Route("/mes-evenements", name="app_adherent_events")
     * @Method("GET")
     */
    public function eventsAction(Request $request): Response
    {
        $manager = $this->get('app.event.registration_manager');

        try {
            $registration = $manager->getAdherentRegistrations($this->getUser(), $request->query->get('type', 'upcoming'));
        } catch (EventRegistrationException $e) {
            throw new BadRequestHttpException('Invalid request parameters.', $e);
        }

        return $this->render('adherent/events.html.twig', [
            'registrations' => $registration,
        ]);
    }

    /**
     * @Route("/contacter/{uuid}", name="app_adherent_contact", requirements={"uuid": "%pattern_uuid%"})
     * @Method("GET|POST")
     */
    public function contactAction(Request $request, Adherent $adherent): Response
    {
        $fromType = $request->query->get('from');
        $fromId = $request->query->get('id');
        $from = null;

        try {
            if ($fromType && $fromId) {
                if ('committee' === $fromType) {
                    $from = $this->getDoctrine()->getRepository(Committee::class)->findOneByUuid($fromId);
                } else {
                    $from = $this->getDoctrine()->getRepository(Event::class)->findOneByUuid($fromId);
                }
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        $message = ContactMessage::createWithCaptcha((string) $request->request->get('g-recaptcha-response'), $this->getUser(), $adherent);

        $form = $this->createForm(ContactMessageType::class, $message);

        try {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->get('app.adherent.contact_message_handler')->handle($message);
                $this->addFlash('info', $this->get('translator')->trans('adherent.contact.success'));

                if ($from instanceof Committee) {
                    return $this->redirectToRoute('app_committee_show', [
                        'slug' => $from->getSlug(),
                    ]);
                }

                if ($from instanceof Event) {
                    return $this->redirectToRoute('app_event_show', [
                        'slug' => $from->getSlug(),
                    ]);
                }

                return $this->redirectToRoute('homepage');
            }
        } catch (ConnectException $e) {
            $this->addFlash('error_recaptcha', $this->get('translator')->trans('recaptcha.error'));
        }

        return $this->render('adherent/contact.html.twig', [
            'adherent' => $adherent,
            'form' => $form->createView(),
            'fromType' => $fromType,
            'from' => $from,
        ]);
    }

    public function listMyCommitteesAction(): Response
    {
        $manager = $this->get('app.committee.manager');

        return $this->render('adherent/list_my_committees.html.twig', [
            'committees' => $manager->getAdherentCommittees($this->getUser()),
        ]);
    }

    /**
     * @Route("/mon-compte/desadherer", name="app_adherent_terminate_membership")
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
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();

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
