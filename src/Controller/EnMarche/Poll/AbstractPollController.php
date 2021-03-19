<?php

namespace App\Controller\EnMarche\Poll;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Poll\LocalPoll;
use App\Entity\Poll\Poll;
use App\Form\Poll\PollType;
use App\Poll\PollManager;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Poll\LocalPollRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractPollController extends AbstractController
{
    use AccessDelegatorTrait;

    protected $localPollRepository;
    protected $zoneRepository;

    public function __construct(LocalPollRepository $localPollRepository, ZoneRepository $zoneRepository)
    {
        $this->localPollRepository = $localPollRepository;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * @Route("", name="local_list", methods={"GET"})
     */
    public function listLocalPolls(Request $request): Response
    {
        return $this->renderTemplate('poll/local_list.html.twig', [
            'polls' => $this->getLocalPolls($this->getMainUser($request->getSession())),
        ]);
    }

    /**
     * @Route(
     *     path="/creer",
     *     name="local_create",
     *     methods={"GET|POST"},
     * )
     */
    public function createLocalPoll(
        Request $request,
        UserInterface $user,
        EntityManagerInterface $manager,
        PollManager $pollManager
    ): Response {
        /** @var Adherent $user */
        $localPoll = new LocalPoll($user);
        $zones = $this->getZones($this->getMainUser($request->getSession()));
        if (1 === \count($zones)) {
            $localPoll->setZone($zones[0]);
        }

        $form = $this
            ->createForm(PollType::class, $localPoll, ['zones' => $zones])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($localPoll);
            $manager->flush();

            if ($localPoll->isPublished()) {
                $pollManager->publish($localPoll);
            }

            $this->addFlash('info', 'poll.create.success');

            return $this->redirectToPollRoute('local_list');
        }

        return $this->renderTemplate('poll/create.html.twig', [
            'form' => $form->createView(),
            'poll' => $localPoll,
        ]);
    }

    /**
     * @Route(
     *     path="/{uuid}/editer",
     *     name="local_edit",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"GET|POST"}
     * )
     *
     * @Security("is_granted('CAN_EDIT_LOCAL_POLL', localPoll)")
     */
    public function editLocalPoll(
        Request $request,
        LocalPoll $localPoll,
        EntityManagerInterface $manager,
        PollManager $pollManager
    ): Response {
        $author = $localPoll->getAuthor();
        if ($editByAuthor = $author === $this->getMainUser($request->getSession())) {
            $zones = $this->getZones($author);
        } else {
            $zones = [$localPoll->getZone()];
        }

        $form = $this
            ->createForm(PollType::class, $localPoll, ['zones' => $zones])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            if ($localPoll->isPublished()) {
                $pollManager->publish($localPoll);
            }

            $this->addFlash('info', 'poll.edit.success');

            return $this->redirectToPollRoute('local_list');
        }

        return $this->renderTemplate('poll/create.html.twig', [
            'form' => $form->createView(),
            'poll' => $localPoll,
        ]);
    }

    /**
     * @Route("/{uuid}/depublier", name="unpublish", methods={"GET"}, defaults={"publish": false})
     * @Route("/{uuid}/publier", name="publish", methods={"GET"}, defaults={"publish": true})
     *
     * @Security("is_granted('CAN_EDIT_LOCAL_POLL', poll)")
     */
    public function togglePublish(bool $publish, Poll $poll, PollManager $pollManager): Response
    {
        if (!($publish xor $poll->isPublished())) {
            $this->addFlash('error', sprintf('La question "%s" est déjà '.($publish ? 'publiée' : 'dépubliée'), $poll->getQuestion()));

            return $this->redirectToPollRoute('local_list');
        }

        if ($publish) {
            $pollManager->publish($poll);
        } else {
            $pollManager->unpublish($poll);
        }

        $this->addFlash('info', sprintf('La question "%s" a bien été '.($publish ? 'publiée' : 'dépubliée'), $poll->getQuestion()));

        return $this->redirectToPollRoute('local_list');
    }

    abstract protected function getSpaceName(): string;

    abstract protected function getZones(Adherent $adherent): array;

    /**
     * @return LocalPoll[]
     */
    protected function getLocalPolls(Adherent $adherent): array
    {
        return $this->localPollRepository->findAllByZonesWithStats($this->getZones($adherent));
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('poll/_base_%s_space.html.twig', $spaceName = $this->getSpaceName()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToPollRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_{$this->getSpaceName()}_polls_${subName}", $parameters);
    }
}
