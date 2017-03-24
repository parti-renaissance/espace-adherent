<?php

namespace AppBundle\Controller;

use AppBundle\Entity\SocialShare;
use AppBundle\Entity\SocialShareCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SocialShareController extends Controller
{
    /**
     * @Route("/jepartage", name="app_social_share_list")
     * @Method("GET")
     */
    public function listAction(): Response
    {
        return $this->render('social/list.html.twig', [
            'socialShareCategories' => $this->getDoctrine()->getRepository(SocialShareCategory::class)->findBy([], ['position' => 'ASC']),
            'socialShares' => $this->getDoctrine()->getRepository(SocialShare::class)->findBy(['published' => true], ['position' => 'ASC', 'createdAt' => 'DESC']),
        ]);
    }

    /**
     * @Route("/jepartage/{slug}", name="app_social_share_show")
     * @Method("GET")
     */
    public function showAction(SocialShareCategory $socialShareCategory): Response
    {
        return $this->render('social/show.html.twig', [
            'socialShareCategory' => $socialShareCategory,
            'socialShares' => $this->getDoctrine()->getRepository(SocialShare::class)->findBy(['socialShareCategory' => $socialShareCategory, 'published' => true], ['position' => 'ASC', 'createdAt' => 'DESC'])
        ]);
    }
}
