<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Committee\CommitteeCreationCommand;
use AppBundle\Contact\ContactMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\EventRegistrationException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\AdherentInterestsFormType;
use AppBundle\Form\ContactMessageType;
use AppBundle\Form\CreateCommitteeCommandType;
use AppBundle\Form\CitizenProjectCommandType;
use AppBundle\CitizenProject\CitizenProjectCreationCommand;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Security\Http\Session\AnonymousFollowerSession;
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
    /**
     * @Route("/accueil", name="app_adherent_home")
     * @Method("GET")
     */
    public function homeAction(): Response
    {
        return $this->render('adherent/home.html.twig');
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
     * This action enables an adherent to create a citizen project.
     *
     * @Route("/creer-mon-projet-citoyen", name="app_adherent_create_citizen_project")
     * @Method("GET|POST")
     */
    public function createCitizenProjectAction(Request $request): Response
    {
        if ($this->isGranted('IS_ANONYMOUS')
            && $authentication = $this->get(AnonymousFollowerSession::class)->start($request)
        ) {
            return $authentication;
        }

        $this->denyAccessUnlessGranted('CREATE_CITIZEN_PROJECT');

        $command = CitizenProjectCreationCommand::createFromAdherent($user = $this->getUser());
        if ($name = $request->query->get('name', false)) {
            $command->name = $name;
        }
        $form = $this->createForm(CitizenProjectCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_project.creation_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_project.creation.success'));

            return $this->redirect($this->generateUrl('app_citizen_project_show', ['slug' => $command->getCitizenProject()->getSlug()]));
        }

        return $this->render('adherent/create_citizen_project.html.twig', [
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
                } elseif ('citizen_project' === $fromType) {
                    $from = $this->getDoctrine()->getRepository(CitizenProject::class)->findOneByUuid($fromId);
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

                if ($from instanceof CitizenProject) {
                    return $this->redirectToRoute('app_citizen_project_show', [
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

    public function listCommitteesAlAction(): Response
    {
        $manager = $this->get('app.committee.manager');

        return $this->render('adherent/list_my_committees_al.html.twig', [
            'committees' => $manager->getAdherentCommittees($this->getUser()),
        ]);
    }

    public function listMyCitizenProjectsAction(CitizenProjectRepository $citizenProjectRepository): Response
    {
        return $this->render('adherent/list_my_citizen_projects.html.twig', [
            'citizen_projects' => $citizenProjectRepository->findAllRegisteredCitizenProjectsForAdherent($this->getUser()),
        ]);
    }

    public function listMyAdministratedCitizenProjectsAction(CitizenProjectRepository $citizenProjectRepository): Response
    {
        return $this->render('adherent/list_my_administrated_citizen_projects.html.twig', [
            'citizen_projects' => $citizenProjectRepository->findAllRegisteredCitizenProjectsForAdherent($this->getUser(), true),
        ]);
    }
}
