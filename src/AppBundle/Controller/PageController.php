<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\Page;
use AppBundle\Entity\Proposal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    /**
     * @Route("/emmanuel-macron", name="page_emmanuel_macron")
     * @Method("GET")
     */
    public function emmanuelMacronAction()
    {
        return $this->render('page/emmanuel-macron/ce-que-je-suis.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('emmanuel-macron-ce-que-je-suis'),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/revolution", name="page_emmanuel_macron_revolution")
     * @Method("GET")
     */
    public function emmanuelMacronRevolutionAction()
    {
        return $this->render('page/emmanuel-macron/revolution.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('emmanuel-macron-revolution'),
        ]);
    }

    /**
     * Redirections to the program.
     *
     * @Route("/programme")
     * @Route("/le-programme")
     * @Method("GET")
     */
    public function redirectProgrammeAction()
    {
        return $this->redirectToRoute('page_emmanuel_macron_programme');
    }

    /**
     * @Route("/emmanuel-macron/le-programme", name="page_emmanuel_macron_programme")
     * @Method("GET")
     */
    public function emmanuelMacronProgrammeAction()
    {
        return $this->render('page/emmanuel-macron/propositions.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('emmanuel-macron-propositions'),

            'proposals' => $this->getDoctrine()->getRepository(Proposal::class)->findAllOrderedByPosition(),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/le-programme/{slug}", name="page_emmanuel_macron_proposition")
     * @Method("GET")
     */
    public function emmanuelMacronPropositionAction($slug)
    {
        $proposal = $this->getDoctrine()->getRepository(Proposal::class)->findOneBySlug($slug);

        if (!$proposal || !$proposal->isPublished()) {
            throw $this->createNotFoundException();
        }

        return $this->render('page/emmanuel-macron/proposition.html.twig', [
            'proposal' => $proposal,
        ]);
    }

    /**
     * @Route("/le-mouvement", name="page_le_mouvement")
     * @Method("GET")
     */
    public function mouvementValeursAction()
    {
        return $this->render('page/le-mouvement/nos-valeurs.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-nos-valeurs'),
        ]);
    }

    /**
     * @Route("/le-mouvement/notre-organisation", name="page_le_mouvement_notre_organisation")
     * @Method("GET")
     */
    public function mouvementOrganisationAction()
    {
        return $this->render('page/le-mouvement/notre-organisation.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-notre-organisation'),
        ]);
    }

    /**
     * @Route("/le-mouvement/la-carte", name="page_le_mouvement_la_carte")
     * @Method("GET")
     */
    public function mouvementCarteAction()
    {
        return $this->render('page/la-carte.html.twig', [
            'userCount' => $this->getDoctrine()->getRepository(Adherent::class)->count(),
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->count(),
            'committeeCount' => $this->getDoctrine()->getRepository(Committee::class)->count(),
        ]);
    }

    /**
     * @Route("/le-mouvement/les-comites", name="page_le_mouvement_les_comites")
     * @Method("GET")
     */
    public function mouvementComitesAction()
    {
        return $this->render('page/le-mouvement/les-comites.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-les-comites'),
        ]);
    }

    /**
     * @Route("/le-mouvement/les-evenements", name="page_le_mouvement_les_evenements")
     * @Method("GET")
     */
    public function mouvementEvenementsAction()
    {
        return $this->render('page/le-mouvement/les-evenements.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-les-evenements'),
        ]);
    }

    /**
     * @Route("/le-mouvement/devenez-benevole", name="page_le_mouvement_devenez_benevole")
     * @Method("GET")
     */
    public function mouvementBenevoleAction()
    {
        return $this->render('page/le-mouvement/devenez-benevole.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-devenez-benevole'),
        ]);
    }

    /**
     * @Route("/mentions-legales", name="page_mentions_legales")
     * @Method("GET")
     */
    public function mentionsLegalesAction()
    {
        return $this->render('page/mentions-legales.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('mentions-legales'),
        ]);
    }

    /**
     * @Route("/bot", name="page_bot")
     * @Method("GET")
     */
    public function botAction()
    {
        return $this->render('page/bot.html.twig');
    }

    /**
     * @Route("/elles-marchent", name="page_elles_marchent")
     * @Method("GET")
     */
    public function ellesMarchentAction()
    {
        return $this->render('page/elles-marchent.html.twig');
    }
}
