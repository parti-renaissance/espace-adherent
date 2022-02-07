<?php

namespace App\Controller\EnMarche;

use App\Committee\CommitteeCreationCommand;
use App\Committee\CommitteeCreationCommandHandler;
use App\Committee\CommitteeManager;
use App\Committee\CommitteePermissions;
use App\Contact\ContactMessage;
use App\Contact\ContactMessageHandler;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\CommitteeEvent;
use App\Event\EventRegistrationManager;
use App\Exception\BadUuidRequestException;
use App\Exception\EventRegistrationException;
use App\Exception\InvalidUuidException;
use App\Form\AdherentInterestsFormType;
use App\Form\ContactMessageType;
use App\Form\CreateCommitteeCommandType;
use App\Geocoder\Exception\GeocodingException;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use App\Search\SearchParametersFilter;
use App\Search\SearchResultsProvidersManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/espace-adherent")
 */
class AdherentController extends AbstractController
{
    /**
     * @Route("/accueil", name="app_adherent_home", methods={"GET"})
     */
    public function homeAction(
        Request $request,
        AdherentRepository $adherentRepository,
        SearchResultsProvidersManager $searchResultsProvidersManager,
        SearchParametersFilter $searchParametersFilter
    ): Response {
        $user = $this->getUser();
        $searchParametersFilter->setCity(sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
        $searchParametersFilter->setMaxResults(3);
        $searchParametersFilter->setRadius(SearchParametersFilter::RADIUS_150);
        $params = [];
        $searchParams = [SearchParametersFilter::TYPE_EVENTS, SearchParametersFilter::TYPE_COMMITTEES];

        foreach ($searchParams as $type) {
            try {
                $searchParametersFilter->setType($type);
                $params[$type] = $searchResultsProvidersManager->find($searchParametersFilter);
            } catch (GeocodingException $exception) {
            }
        }

        if ($request->query->getBoolean('from_activation')) {
            $this->addFlash('info', 'adherent.activation.success');
        }

        return $this->render('adherent/home.html.twig', array_merge([
            'nb_adherent' => $adherentRepository->countAdherents(),
            'from_activation' => $request->query->getBoolean('from_activation'),
        ], $params));
    }

    /**
     * This action enables an adherent to pin his/her interests.
     *
     * @Route("/mon-compte/centres-d-interet", name="app_adherent_pin_interests", methods={"GET", "POST"})
     */
    public function pinInterestsAction(
        EntityManagerInterface $manager,
        Request $request,
        EventDispatcherInterface $dispatcher
    ): Response {
        $form = $this
            ->createForm(AdherentInterestsFormType::class, $adherent = $this->getUser())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('info', 'adherent.update_interests.success');

            $dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATE_INTERESTS);

            return $this->redirectToRoute('app_adherent_pin_interests');
        }

        return $this->render('adherent/pin_interests.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * This action enables an adherent to create a committee.
     *
     * @Route("/creer-mon-comite", name="app_adherent_create_committee", methods={"GET", "POST"})
     */
    public function createCommitteeAction(Request $request, CommitteeCreationCommandHandler $commandHandler): Response
    {
        $command = CommitteeCreationCommand::createFromAdherent($user = $this->getUser());
        $form = $this->createForm(CreateCommitteeCommandType::class, $command, ['validation_groups' => ['Default', 'created_by_adherent']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted(CommitteePermissions::CREATE)) {
                throw $this->createAccessDeniedException();
            }

            $commandHandler->handle($command);
            $this->addFlash('info', 'committee.creation.success.adherent');

            return $this->redirectToRoute('app_adherent_profile_activity', ['_fragment' => 'committees']);
        }

        return $this->render('adherent/create_committee.html.twig', [
            'form' => $form->createView(),
            'adherent' => $user,
        ]);
    }

    /**
     * @Route("/mes-comites", name="app_adherent_committees", methods={"GET"})
     */
    public function committeesAction(CommitteeManager $manager, UserInterface $adherent): Response
    {
        return $this->render('adherent/my_activity_committees.html.twig', [
            'committeeMemberships' => $manager->getCommitteeMembershipsForAdherent($adherent),
        ]);
    }

    /**
     * @Route("/mes-evenements", name="app_adherent_events", methods={"GET"})
     */
    public function eventsAction(Request $request, EventRegistrationManager $manager): Response
    {
        try {
            $registration = $manager->getAdherentRegistrations($this->getUser(), $request->query->get('type', 'upcoming'));
        } catch (EventRegistrationException $e) {
            throw new BadRequestHttpException('Invalid request parameters.', $e);
        }

        return $this->render('adherent/my_activity_events.html.twig', [
            'registrations' => $registration,
        ]);
    }

    /**
     * @Route("/contacter/{uuid}", name="app_adherent_contact", requirements={"uuid": "%pattern_uuid%"}, methods={"GET", "POST"})
     */
    public function contactAction(
        Request $request,
        Adherent $adherent,
        ContactMessageHandler $handler,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $fromType = $request->query->get('from');
        $fromId = $request->query->get('id');
        $from = null;

        try {
            if ($fromType && $fromId) {
                if ('committee' === $fromType) {
                    $from = $entityManager->getRepository(Committee::class)->findOneByUuid($fromId);
                } elseif ('territorial_council' === $fromType || 'political_committee' === $fromType) {
                    $from = true;
                } else {
                    $from = $entityManager->getRepository(CommitteeEvent::class)->findOneByUuid($fromId);
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
                $handler->handle($message);
                $this->addFlash('info', 'adherent.contact.success');

                if ($from instanceof Committee) {
                    return $this->redirectToRoute('app_committee_show', [
                        'slug' => $from->getSlug(),
                    ]);
                }

                if ($from instanceof CommitteeEvent) {
                    return $this->redirectToRoute('app_committee_event_show', [
                        'slug' => $from->getSlug(),
                    ]);
                }

                if ('territorial_council' === $fromType) {
                    return $this->redirectToRoute('app_territorial_council_index');
                } elseif ('political_committee' === $fromType) {
                    return $this->redirectToRoute('app_political_committee_index');
                }

                return $this->redirectToRoute('homepage');
            }
        } catch (ExceptionInterface $e) {
            $this->addFlash('error_recaptcha', $translator->trans('recaptcha.error'));
        }

        return $this->render('adherent/contact.html.twig', [
            'adherent' => $adherent,
            'form' => $form->createView(),
            'fromType' => $fromType,
            'from' => $from,
        ]);
    }
}
