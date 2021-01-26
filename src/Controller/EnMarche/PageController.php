<?php

namespace App\Controller\EnMarche;

use App\Entity\Page;
use App\Repository\FacebookVideoRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Each time you add or update a custom url with an hardcoded slug in the controller code, you must update the
 * App\Entity\Page::URLS constant and reindex algolia's page index.
 */
class PageController extends AbstractController
{
    /**
     * @Route("/formation", name="page_campus", methods={"GET"})
     */
    public function campusAction(): Response
    {
        return $this->render('page/campus/home.html.twig');
    }

    /**
     * @Route("/formation/difficultes-internet", name="page_campus_internet", methods={"GET"})
     */
    public function campusInternetAction(): Response
    {
        return $this->render('page/campus/internet.html.twig');
    }

    /**
     * @Route("/emmanuel-macron", name="page_emmanuel_macron", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('emmanuel-macron')")
     */
    public function emmanuelMacronAction(Page $page): Response
    {
        return $this->render('page/emmanuel-macron/ce-que-je-suis.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/emmanuel-macron/revolution", name="page_emmanuel_macron_revolution", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('emmanuel-macron/revolution')")
     */
    public function emmanuelMacronRevolutionAction(Page $page): Response
    {
        return $this->render('page/emmanuel-macron/revolution.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/emmanuel-macron/videos", name="page_emmanuel_macron_videos", methods={"GET"})
     */
    public function emmanuelMacronVideosAction(FacebookVideoRepository $repository): Response
    {
        return $this->render('page/emmanuel-macron/videos.html.twig', [
            'videos' => $repository->findPublishedVideos(),
        ]);
    }

    /**
     * @Route("/le-mouvement", name="page_le_mouvement", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('le-mouvement')")
     */
    public function mouvementValeursAction(Page $page): Response
    {
        return $this->render('page/le-mouvement/nos-valeurs.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/le-mouvement/legislatives", name="page_le_mouvement_legislatives", methods={"GET"})
     */
    public function mouvementLegislativesAction(): Response
    {
        return $this->redirect('https://legislatives.en-marche.fr', Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/le-mouvement/les-comites", name="page_le_mouvement_les_comites", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('le-mouvement/les-comites')")
     */
    public function mouvementComitesAction(Page $page): Response
    {
        return $this->render('page/le-mouvement/les-comites.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/le-mouvement/devenez-benevole", name="page_le_mouvement_devenez_benevole", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('le-mouvement/devenez-benevole')")
     */
    public function mouvementBenevoleAction(Page $page): Response
    {
        return $this->render('page/le-mouvement/devenez-benevole.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/mentions-legales", name="page_mentions_legales", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('mentions-legales')")
     */
    public function mentionsLegalesAction(Page $page): Response
    {
        return $this->render('page/mentions-legales.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/politique-cookies", name="page_politique_cookies", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('politique-cookies')")
     */
    public function politiqueCookiesAction(Page $page): Response
    {
        return $this->render('page/politique-cookies.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/okcandidatlegislatives", name="legislatives_confirm_newsletter", methods={"GET"})
     */
    public function legislativesConfirmNewsletterAction(): Response
    {
        return $this->render('legislative_candidate/confirm_newsletter.html.twig');
    }

    /**
     * @Route("/elles-marchent", name="page_elles_marchent", methods={"GET"})
     */
    public function ellesMarchentAction(): Response
    {
        return $this->render('page/elles-marchent.html.twig');
    }

    /**
     * @Route("/candidatures-delegue-general-et-bureau-executif", name="page_burex", methods={"GET"})
     */
    public function burexAction(): Response
    {
        return $this->render('page/burex-lists.html.twig');
    }

    /**
     * @Route("/1000-talents", name="page_1000_talents", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('1000-talents')")
     */
    public function page1000TalentsAction(Page $page): Response
    {
        return $this->render('page/talents/1000-talents/home.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/cestduconcret", name="page_concrete", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('concrete')")
     */
    public function concreteAction(Page $page): Response
    {
        return $this->render('page/concrete/home.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/action-talents", name="page_action_talents", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('action-talents')")
     */
    public function actionTalentsAction(Page $page): Response
    {
        return $this->render('page/talents/action-talents/home.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/grande-marche-europe", name="page_grande_marche_europe", methods={"GET"})
     */
    public function grandeMarcheEuropeAction(): Response
    {
        return $this->render('page/grande-marche-europe/grande-marche-europe.html.twig');
    }

    /**
     * @Route("/action-talents/candidater", name="page_action_talents_apply", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('action-talents/candidater')")
     */
    public function actionTalentsApplicationAction(Page $page): Response
    {
        return $this->render('page/talents/action-talents/apply.html.twig', ['page' => $page]);
    }

    /**
     * @Route("/nos-offres", name="page_jobs", methods={"GET"})
     * @Entity("page", expr="repository.findOneBySlug('nos-offres')")
     */
    public function jobsAction(Page $page): Response
    {
        return $this->render('page/jobs.html.twig', ['page' => $page]);
    }

    /**
     * @Entity("page", expr="repository.findOneBySlug(slug)")
     */
    public function showPageAction(Page $page): Response
    {
        return $this->render(sprintf('page/layout/%s.html.twig', $page->getLayout()), ['page' => $page]);
    }
}
