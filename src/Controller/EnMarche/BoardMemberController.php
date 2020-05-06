<?php

namespace App\Controller\EnMarche;

use App\BoardMember\BoardMemberFilter;
use App\BoardMember\BoardMemberMessage;
use App\Entity\Adherent;
use App\Entity\BoardMember\BoardMember;
use App\Form\BoardMemberMessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-membres-conseil")
 * @Security("is_granted('ROLE_BOARD_MEMBER')")
 */
class BoardMemberController extends Controller
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
    public function searchAction(Request $request): Response
    {
        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_board_member_search');
        }

        $results = $this->get('app.board_member.manager')->paginateMembers($filter, $this->getUser());

        $filter->setToken($this->get('security.csrf.token_manager')->getToken(self::TOKEN_ID));

        return $this->render('board_member/search.html.twig', [
            'filter' => $filter,
            'has_filter' => $request->query->has(BoardMemberFilter::PARAMETER_TOKEN),
            'results' => $results,
            'areas' => BoardMember::AREAS_CHOICES,
            'roles' => $this->get('app.board_member.manager')->findRoles(),
        ]);
    }

    /**
     * @Route("/profils-sauvegardes", name="app_board_member_saved_profile", methods={"GET"})
     */
    public function savedProfilAction()
    {
        $savedMembers = $this->get('app.board_member.manager')->findSavedMembers($this->getUser());
        $statistics = $this->get('app.board_member.manager')->getStatistics($savedMembers);

        return $this->render('board_member/saved_profile.html.twig', [
            'results' => $savedMembers,
            'stats' => $statistics,
        ]);
    }

    /**
     * @Route("/recherche/message", name="app_board_member_message_search", methods={"GET", "POST"})
     */
    public function sendMessageToSearchResultsAction(Request $request): Response
    {
        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        if (!$filter->hasToken() || !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_board_member_search');
        }

        $recipients = $this->get('app.board_member.manager')->searchMembers($filter, $this->getUser());
        $message = $this->createMessage($recipients);

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.board_member.message_notifier')->sendMessage($message);

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
    public function sendMessageToSavedProfilesAction(Request $request): Response
    {
        $recipients = $this->get('app.board_member.manager')->findSavedMembers($this->getUser());
        $message = $this->createMessage($recipients->toArray());

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.board_member.message_notifier')->sendMessage($message);

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
    public function sendMessageToMemberAction(Request $request, Adherent $member): Response
    {
        $message = $this->createMessage([$member]);

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.board_member.message_notifier')->sendMessage($message);

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
    public function addBoardMemberOnListAction(Request $request)
    {
        if (!$id = $request->request->getInt('boardMemberId')) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $boardMemberRepository = $this->getDoctrine()->getManager()->getRepository(BoardMember::class);

        if (!$boadMemberToAdd = $boardMemberRepository->find($id)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $currentBoardMember = $boardMemberRepository->findOneBy(['adherent' => $this->getUser()]);
        $currentBoardMember->addSavedBoardMember($boadMemberToAdd);

        $this->getDoctrine()->getManager()->persist($currentBoardMember);
        $this->getDoctrine()->getManager()->flush();

        return new Response('', Response::HTTP_CREATED);
    }

    /**
     * @Route("/list/boardmember/{boardMemberId}", name="app_board_remove_profile_on_list", methods={"DELETE"})
     */
    public function deleteBoardMemberOnListAction($boardMemberId)
    {
        $boardMemberRepository = $this->getDoctrine()->getRepository(BoardMember::class);

        $boadMemberToDelete = $boardMemberRepository->find($boardMemberId);
        if (null === $boadMemberToDelete) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
        $currentBoardMember = $boardMemberRepository->findOneBy(['adherent' => $this->getUser()]);

        $currentBoardMember->removeSavedBoardMember($boadMemberToDelete);

        $this->getDoctrine()->getManager()->persist($currentBoardMember);
        $this->getDoctrine()->getManager()->flush();

        return new Response('', Response::HTTP_OK);
    }
}
