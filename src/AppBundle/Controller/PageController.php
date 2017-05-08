<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Clarification;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\FacebookVideo;
use AppBundle\Entity\Page;
use AppBundle\Entity\Proposal;
use AppBundle\Event\EventCategories;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Each time you add or update a custom url with an harcorded slug in the controller code, you must update the
 * AppBundle\Entity\Page::URLS constant and reindex algolia's page index.
 */
class PageController extends Controller
{
    /**
     * @Route("/emmanuel-macron", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron")
     * @Method("GET")
     */
    public function emmanuelMacronAction()
    {
        return $this->render('page/emmanuel-macron/ce-que-je-suis.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('emmanuel-macron-ce-que-je-suis'),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/revolution", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_revolution")
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
     * @Route("/programme", defaults={"_enable_campaign_silence"=true})
     * @Route("/le-programme", defaults={"_enable_campaign_silence"=true})
     * @Method("GET")
     */
    public function redirectProgrammeAction()
    {
        return $this->redirectToRoute('page_emmanuel_macron_programme', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/emmanuel-macron/le-programme", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_programme")
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
     * @Route("/emmanuel-macron/le-programme/{slug}", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_proposition")
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
     * @Route("/emmanuel-macron/desintox", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_desintox_list")
     * @Method("GET")
     */
    public function emmanuelMacronDesintoxListAction()
    {
        $repository = $this->getDoctrine()->getRepository(Clarification::class);

        return $this->render('page/emmanuel-macron/desintox.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('desintox'),
            'clarifications' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/desintox/{slug}", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_desintox_view")
     * @Method("GET")
     */
    public function emmanuelMacronDesintoxViewAction($slug)
    {
        $clarification = $this->getDoctrine()->getRepository(Clarification::class)->findOneBySlug($slug);

        if (!$clarification || !$clarification->isPublished()) {
            throw $this->createNotFoundException();
        }

        return $this->render('page/emmanuel-macron/desintox_view.html.twig', [
            'clarification' => $clarification,
        ]);
    }

    /**
     * @Route("/emmanuel-macron/videos", defaults={"_enable_campaign_silence"=true}, name="page_emmanuel_macron_videos")
     * @Method("GET")
     */
    public function emmanuelMacronVideosAction()
    {
        return $this->render('page/emmanuel-macron/videos.html.twig', [
            'videos' => $this->getDoctrine()->getRepository(FacebookVideo::class)->findBy(['published' => true], ['position' => 'ASC']),
        ]);
    }

    /**
     * @Route("/le-mouvement", defaults={"_enable_campaign_silence"=true}, name="page_le_mouvement")
     * @Method("GET")
     */
    public function mouvementValeursAction()
    {
        return $this->render('page/le-mouvement/nos-valeurs.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-nos-valeurs'),
        ]);
    }

    /**
     * @Route("/le-mouvement/notre-organisation", defaults={"_enable_campaign_silence"=true}, name="page_le_mouvement_notre_organisation")
     * @Method("GET")
     */
    public function mouvementOrganisationAction()
    {
        return $this->render('page/le-mouvement/notre-organisation.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-notre-organisation'),
        ]);
    }

    /**
     * @Route("/le-mouvement/legislatives", defaults={"_enable_campaign_silence"=true}, name="page_le_mouvement_legislatives")
     * @Method("GET")
     */
    public function mouvementLegislativesAction()
    {
        return $this->render('page/le-mouvement/legislatives.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('le-mouvement-legislatives'),
        ]);
    }

    /**
     * @Route("/le-mouvement/la-carte", name="page_le_mouvement_la_carte")
     * @Method("GET")
     */
    public function mouvementCarteComitesAction()
    {
        return $this->render('page/la-carte.html.twig', [
            'userCount' => $this->getDoctrine()->getRepository(Adherent::class)->count(),
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->count(),
            'committeeCount' => $this->getDoctrine()->getRepository(Committee::class)->count(),
        ]);
    }

    /**
     * @Route("/evenements/la-carte", name="page_les_evenements_la_carte")
     * @Method("GET")
     */
    public function mouvementCarteEvenementsAction()
    {
        return $this->render('page/les-evenements/la-carte.html.twig', [
            'eventCount' => $this->getDoctrine()->getRepository(Event::class)->countUpcomingEvents(),
            'types' => EventCategories::CHOICES,
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
     * @Route("/mentions-legales", defaults={"_enable_campaign_silence"=true}, name="page_mentions_legales")
     * @Method("GET")
     */
    public function mentionsLegalesAction()
    {
        return $this->render('page/mentions-legales.html.twig', [
            'page' => $this->getDoctrine()->getRepository(Page::class)->findOneBySlug('mentions-legales'),
        ]);
    }

    /**
     * @Route("/okcandidatlegislatives", name="legislatives_confirm_newsletter")
     * @Method("GET")
     */
    public function legislativesConfirmNewsletterAction()
    {
        return $this->render('legislatives/confirm_newsletter.html.twig');
    }

    /**
     * @Route("/bot", name="page_bot")
     * @Method("GET")
     */
    public function botAction()
    {
        return $this->render('bot/page.html.twig');
    }

    /**
     * @Route("/elles-marchent", defaults={"_enable_campaign_silence"=true}, name="page_elles_marchent")
     * @Method("GET")
     */
    public function ellesMarchentAction()
    {
        return $this->render('page/elles-marchent.html.twig');
    }
}
