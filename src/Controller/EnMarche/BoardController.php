<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\BoardMember\BoardMemberFilter;
use AppBundle\Entity\Adherent;
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
class BoardController extends Controller
{
    const TOKEN_ID = 'board_search';

    /**
     * @Route("/", defaults={"_enable_campaign_silence"=true}, name="app_board_home")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('board/home.html.twig', [
          'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/recherche", defaults={"_enable_campaign_silence"=true}, name="app_board_search")
     * @Method("GET")
     */
    public function searchAction(Request $request): Response
    {
        $filter = new BoardMemberFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_board_search');
        }

        $repository = $this->getDoctrine()->getRepository(Adherent::class);
        $results = $repository->searchBoardMembers($filter->hasToken() ? $filter : new BoardMemberFilter());

        $filter->setToken($this->get('security.csrf.token_manager')->getToken(self::TOKEN_ID));

        return $this->render('board/search.html.twig', [
            'filter' => $filter,
            'has_filter' => $request->query->has(BoardMemberFilter::PARAMETER_TOKEN),
            'results_count' => $results->count(),
            'results' => $results->getQuery()->getResult(),
        ]);
    }

    /**
     * @Route("/profils-sauvegardes", defaults={"_enable_campaign_silence"=true}, name="app_board_saved_profile")
     * @Method("GET")
     */
    public function savedProfilAction()
    {
        return $this->render('board/saved_profile.html.twig');
    }
}
