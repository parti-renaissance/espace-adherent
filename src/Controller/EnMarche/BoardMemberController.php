<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\BoardMember\BoardMemberFilter;
use AppBundle\BoardMember\BoardMemberMessage;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Form\BoardMemberMessageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-membres-conseil")
 * @Security("is_granted('ROLE_BOARD_MEMBER')")
 */
class BoardMemberController extends Controller
{
    const TOKEN_ID = 'board_member_search';

    /**
     * @Route("/", defaults={"_enable_campaign_silence"=true}, name="app_board_member_home")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('board_member/home.html.twig');
    }

    /**
     * @Route("/recherche", defaults={"_enable_campaign_silence"=true}, name="app_board_member_search")
     * @Method("GET")
     */
    public function searchAction(Request $request): Response
    {
        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_board_member_search');
        }

        $results = $this->get('app.board_member.manager')->paginateMembers($filter);

        $filter->setToken($this->get('security.csrf.token_manager')->getToken(self::TOKEN_ID));

        return $this->render('board_member/search.html.twig', [
            'filter' => $filter,
            'has_filter' => $request->query->has(BoardMemberFilter::PARAMETER_TOKEN),
            'results_count' => $results->count(),
            'results' => $results->getQuery()->getResult(),
            'areas' => BoardMember::AREAS_CHOICES,
            'roles' => $this->get('app.board_member.manager')->findRoles(),
        ]);
    }

    /**
     * @Route("/profils-sauvegardes", defaults={"_enable_campaign_silence"=true}, name="app_board_member_saved_profile")
     * @Method("GET")
     */
    public function savedProfilAction()
    {
        $results = $this->get('app.board_member.manager')->findSavedMembers($this->getUser());

        return $this->render('board_member/saved_profile.html.twig', [
            'results' => $results,
            'results_count' => count($results),
        ]);
    }

    /**
     * @Route("/recherche/message", name="app_board_member_message_search")
     * @Method("GET|POST")
     */
    public function sendMessageToSearchResultsAction(Request $request): Response
    {
        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        if (!$filter->hasToken() || !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_board_member_search');
        }

        $recipients = $this->get('app.board_member.manager')->searchMembers($filter);
        $message = $this->createMessage($recipients);

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sendMessage($message);

            return $this->redirect($this->generateUrl('app_board_member_message_saved_profile'));
        }

        return $this->render('board_member/message/search.html.twig', [
            'filter' => $filter,
            'form' => $form->createView(),
            'message' => $message,
            'roles' => $this->get('app.board_member.manager')->findRoleChoices(),
        ]);
    }

    /**
     * @Route("/profils-sauvegardes/message", name="app_board_member_message_saved_profile")
     * @Method("GET|POST")
     */
    public function sendMessageToSavedProfilesAction(Request $request): Response
    {
        $recipients = $this->get('app.board_member.manager')->findSavedMembers($this->getUser());
        $message = $this->createMessage($recipients);

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sendMessage($message);

            return $this->redirect($this->generateUrl('app_board_member_message_saved_profile'));
        }

        return $this->render('board_member/message/saved_profile.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    /**
     * @Route("/message/{member}", name="app_board_member_message_member")
     * @Method("GET|POST")
     */
    public function sendMessageToMemberAction(Request $request, Adherent $member): Response
    {
        $message = $this->createMessage([$member]);

        $form = $this->createForm(BoardMemberMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->sendMessage($message);

            return $this->redirect($this->generateUrl('app_board_member_message_member', ['member' => $member->getId()]));
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

    private function sendMessage(BoardMemberMessage $message): void
    {
        $this->get('app.board_member.message_notifier')->sendMessage($message);

        $this->addFlash('info', $this->get('translator')->trans('board_member.message.success'));
    }
}
