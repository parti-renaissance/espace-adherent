<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{
    /**
     * @Route(
     *     "/emmanuel-macron/{slug}",
     *     requirements={"slug"="ce-que-je-suis|revolution|mes-propositions|mon-agenda"},
     *     defaults={"slug"="ce-que-je-suis"},
     *     name="page_emmanuel_macron"
     * )
     * @Method("GET")
     */
    public function emmanuelMacronAction(Request $request, $slug)
    {
        if ('/emmanuel-macron/ce-que-je-suis' === $request->getPathInfo()) {
            return $this->redirectToRoute('page_emmanuel_macron', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->get('app.cloudflare')->cacheIndefinitely(
            $this->render('page/emmanuel-macron/'.$slug.'.html.twig'),
            ['pages', 'page-emmanuel-macron', 'page-emmanuel-macron-'.$slug]
        );
    }

    /**
     * @Route(
     *     "/le-mouvement/{slug}",
     *     requirements={"slug"="nos-valeurs|notre-organisation|les-comites|les-evenements|devenez-benevole"},
     *     defaults={"slug"="nos-valeurs"},
     *     name="page_le_mouvement"
     * )
     * @Method("GET")
     */
    public function mouvementAction(Request $request, $slug)
    {
        if ('/le-mouvement/nos-valeurs' === $request->getPathInfo()) {
            return $this->redirectToRoute('page_le_mouvement', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->get('app.cloudflare')->cacheIndefinitely(
            $this->render('page/le-mouvement/'.$slug.'.html.twig'),
            ['pages', 'page-le-mouvement', 'page-le-mouvement-'.$slug]
        );
    }

    /**
     * @Route("/programme", name="page_programme")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->get('app.cloudflare')->cacheIndefinitely(
            $this->render('page/programme.html.twig'),
            ['pages', 'page-programme']
        );
    }
}
