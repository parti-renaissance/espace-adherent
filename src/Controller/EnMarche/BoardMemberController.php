<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BoardMember\BoardMember;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/espace-membres-conseil")
 * @Security("is_granted('ROLE_BOARD_MEMBER')")
 */
class BoardMemberController extends Controller
{
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
    public function searchAction()
    {
        return $this->render('board_member/search.html.twig');
    }

    /**
     * @Route("/profils-sauvegardes", defaults={"_enable_campaign_silence"=true}, name="app_board_member_saved_profile")
     * @Method("GET")
     */
    public function savedProfilAction()
    {
        $adherentRepository = $this->getDoctrine()->getRepository(Adherent::class);
        $owner = $this->getDoctrine()->getRepository(BoardMember::class)->findOneByAdherent(['id' => $this->getUser()]);

        return $this->render('board_member/saved_profile.html.twig', [
            'boardMembers' => $adherentRepository->findSavedBoardMember($owner),
        ]);
    }
}
