<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\SocialShare;
use AppBundle\Entity\SocialShareCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SocialShareController extends Controller
{
    /**
     * @Route("/jepartage", name="app_social_share_list", methods={"GET"})
     */
    public function listAction(): Response
    {
        $manager = $this->getDoctrine();

        return $this->render('social_share/wall.html.twig', [
            'currentCategory' => null,
            'socialShareCategories' => $manager->getRepository(SocialShareCategory::class)->findForWall(),
            'socialShares' => $manager->getRepository(SocialShare::class)->findForWall(),
        ]);
    }

    /**
     * @Route("/jepartage/{slug}", name="app_social_share_show", methods={"GET"})
     */
    public function showAction(SocialShareCategory $category): Response
    {
        $manager = $this->getDoctrine();

        return $this->render('social_share/wall.html.twig', [
            'currentCategory' => $category,
            'socialShareCategories' => $manager->getRepository(SocialShareCategory::class)->findForWall(),
            'socialShares' => $this->getDoctrine()->getRepository(SocialShare::class)->findForWall($category),
        ]);
    }

    /**
     * @Route("/je-partage", methods={"GET"})
     */
    public function redirectAction(): Response
    {
        return $this->redirectToRoute('app_social_share_list', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
