<?php

namespace App\Controller\EnMarche;

use App\AppCodeEnum;
use App\Committee\CommandHandler\CommitteeCreationCommandHandler;
use App\Committee\CommitteePermissionEnum;
use App\Committee\DTO\CommitteeCreationCommand;
use App\Contact\ContactMessage;
use App\Contact\ContactMessageHandler;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\Event;
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
use App\OAuth\App\AuthAppUrlManager;
use App\Repository\AdherentRepository;
use App\Search\SearchParametersFilter;
use App\Search\SearchResultsProvidersManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/espace-adherent')]
class AdherentController extends AbstractController
{
    #[Route(path: '/accueil', name: 'app_adherent_home', methods: ['GET'])]
    public function homeAction(
        Request $request,
        AdherentRepository $adherentRepository,
        SearchResultsProvidersManager $searchResultsProvidersManager,
        SearchParametersFilter $searchParametersFilter,
    ): Response {
        $user = $this->getUser();
        $searchParametersFilter->setCity(\sprintf('%s, %s', $user->getCityName(), $user->getCountryName()));
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
     */
    #[Route(path: '/mon-compte/centres-d-interet', name: 'app_adherent_pin_interests', methods: ['GET', 'POST'])]
    public function pinInterestsAction(
        EntityManagerInterface $manager,
        Request $request,
        EventDispatcherInterface $dispatcher,
        AuthAppUrlManager $appUrlManager,
    ): Response {
        $appCode = $appUrlManager->getAppCodeFromRequest($request);
        $isRenaissanceApp = AppCodeEnum::isRenaissanceApp($appCode);

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$isRenaissanceApp && $adherent->isRenaissanceUser()) {
            return $this->render('adherent/renaissance_profile.html.twig');
        }

        $form = $this
            ->createForm(AdherentInterestsFormType::class, $adherent)
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
     */
    #[Route(path: '/creer-mon-comite', name: 'app_adherent_create_committee', methods: ['GET', 'POST'])]
    public function createCommitteeAction(Request $request, CommitteeCreationCommandHandler $commandHandler): Response
    {
        $command = CommitteeCreationCommand::createFromAdherent($user = $this->getUser());
        $form = $this->createForm(CreateCommitteeCommandType::class, $command, ['validation_groups' => ['Default', 'created_by_adherent']]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted(CommitteePermissionEnum::CREATE)) {
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

    #[Route(path: '/mes-comites', name: 'app_adherent_committees', methods: ['GET'])]
    public function committeesAction(UserInterface $adherent): Response
    {
        /** @var Adherent $adherent */
        $committeeMembership = $adherent->getCommitteeMembership();

        return $this->render('adherent/my_activity_committees.html.twig', [
            'committeeMemberships' => $committeeMembership ? [$committeeMembership] : [],
        ]);
    }

    #[Route(path: '/mes-evenements', name: 'app_adherent_events', methods: ['GET'])]
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

    #[Route(path: '/contacter/{uuid}', name: 'app_adherent_contact', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
    public function contactAction(
        Request $request,
        Adherent $adherent,
        ContactMessageHandler $handler,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
    ): Response {
        $fromType = $request->query->get('from');
        $fromId = $request->query->get('id');
        $from = null;

        try {
            if ($fromType && $fromId) {
                if ('committee' === $fromType) {
                    $from = $entityManager->getRepository(Committee::class)->findOneByUuid($fromId);
                } else {
                    $from = $entityManager->getRepository(Event::class)->findOneByUuid($fromId);
                }
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        $message = ContactMessage::createWithCaptcha($this->getUser(), $adherent, $request->request->get('g-recaptcha-response'));

        $form = $this->createForm(ContactMessageType::class, $message, ['validation_groups' => ['Default', 'em_event_contact_organizer']]);

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

                if ($from->getCommittee()) {
                    return $this->redirectToRoute('app_committee_event_show', [
                        'slug' => $from->getSlug(),
                    ]);
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
