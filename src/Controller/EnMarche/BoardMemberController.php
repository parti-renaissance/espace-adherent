<?php

namespace App\Controller\EnMarche;

use App\BoardMember\BoardMemberFilter;
use App\BoardMember\BoardMemberManager;
use App\BoardMember\BoardMemberMessage;
use App\BoardMember\BoardMemberMessageNotifier;
use App\Entity\Adherent;
use App\Entity\BoardMember\BoardMember;
use App\Form\BoardMemberMessageType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * @Route("/espace-membres-conseil")
 * @Security("is_granted('ROLE_BOARD_MEMBER')")
 */
class BoardMemberController extends AbstractController
{
    const TOKEN_ID = 'board_member_search';

    /**
     * @Route("/", name="app_board_member_home", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->render('board_member/home.html.twig');
    }

    /**
     * @Route("/recherche", name="app_board_member_search", methods={"GET"})
     */
    public function searchAction(
        Request $request,
        BoardMemberManager $manager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_board_member_search');
        }

        $results = $manager->paginateMembers($filter, $this->getUser());

        $filter->setToken($csrfTokenManager->getToken(self::TOKEN_ID));

        return $this->render('board_member/search.html.twig', [
            'filter' => $filter,
            'has_filter' => $request->query->has(BoardMemberFilter::PARAMETER_TOKEN),
            'results' => $results,
            'areas' => BoardMember::AREAS_CHOICES,
            'roles' => $manager->findRoles(),
        ]);
    }

    /**
     * @Route("/profils-sauvegardes", name="app_board_member_saved_profile", methods={"GET"})
     */
    public function savedProfilAction(BoardMemberManager $manager): Response
    {
        $savedMembers = $manager->findSavedMembers($this->getUser());
        $statistics = $manager->getStatistics($savedMembers);

        return $this->render('board_member/saved_profile.html.twig', [
            'results' => $savedMembers,
            'stats' => $statistics,
        ]);
    }

    /**
     * @Route("/recherche/message", name="app_board_member_message_search", methods={"GET", "POST"})
     */
    public function sendMessageToSearchResultsAction(
        Request $request,
        BoardMemberManager $manager,
        BoardMemberMessageNotifier $notifier
    ): Response {
        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        if (!$filter->hasToken() || !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_board_member_search');
        }

        $recipients = $manager->searchMembers($filter, $this->getUser());
        $message = $this->createMessage($recipients);

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notifier->sendMessage($message);

            $this->addFlash('info', 'board_member.message.success');

            return $this->redirectToRoute('app_board_member_search');
        }

        return $this->render('board_member/message/search.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
            'filter' => $filter,
        ]);
    }

    /**
     * @Route("/profils-sauvegardes/message", name="app_board_member_message_saved_profile", methods={"GET", "POST"})
     */
    public function sendMessageToSavedProfilesAction(
        Request $request,
        BoardMemberManager $manager,
        BoardMemberMessageNotifier $notifier
    ): Response {
        $recipients = $manager->findSavedMembers($this->getUser());
        $message = $this->createMessage($recipients->toArray());

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notifier->sendMessage($message);

            $this->addFlash('info', 'board_member.message.success');

            return $this->redirectToRoute('app_board_member_message_saved_profile');
        }

        return $this->render('board_member/message/saved_profile.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    /**
     * @Route("/message/{member}", name="app_board_member_message_member", methods={"GET", "POST"})
     */
    public function sendMessageToMemberAction(
        Request $request,
        Adherent $member,
        BoardMemberMessageNotifier $notifier
    ): Response {
        $message = $this->createMessage([$member]);

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notifier->sendMessage($message);

            $this->addFlash('info', 'board_member.message.success');

            return $this->redirectToRoute('app_board_member_search');
        }

        return $this->render('board_member/message/member.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    private function createMessage(array $recipients): BoardMemberMessage
    {
        return new BoardMemberMessage($this->getUser(), $recipients);
    }

    /**
     * @Route("/list/boardmember", name="app_board_add_profile_on_list", methods={"POST"})
     */
    public function addBoardMemberOnListAction(Request $request, EntityManagerInterface $manager): Response
    {
        if (!$id = $request->request->getInt('boardMemberId')) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $boardMemberRepository = $manager->getRepository(BoardMember::class);

        if (!$boadMemberToAdd = $boardMemberRepository->find($id)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $currentBoardMember = $boardMemberRepository->findOneBy(['adherent' => $this->getUser()]);
        $currentBoardMember->addSavedBoardMember($boadMemberToAdd);

        $manager->persist($currentBoardMember);
        $manager->flush();

        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * @Route("/list/boardmember/{id}", name="app_board_remove_profile_on_list", methods={"DELETE"})
     */
    public function deleteBoardMemberOnListAction(
        EntityManagerInterface $entityManager,
        BoardMember $boadMemberToDelete
    ): Response {
        $boardMemberRepository = $entityManager->getRepository(BoardMember::class);

        $currentBoardMember = $boardMemberRepository->findOneBy(['adherent' => $this->getUser()]);
        $currentBoardMember->removeSavedBoardMember($boadMemberToDelete);

        $entityManager->persist($currentBoardMember);
        $entityManager->flush();

        return new Response('', Response::HTTP_OK);
    }
}
