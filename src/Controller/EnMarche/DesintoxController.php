<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Clarification;
use AppBundle\Entity\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Each time you add or update a custom url with an harcorded slug in the controller code, you must update the
 * AppBundle\Entity\Page::URLS constant and reindex algolia's page index.
 */
class DesintoxController extends Controller
{
    /**
     * @Route("/emmanuel-macron/desintox", defaults={"_enable_campaign_silence"=true}, name="desintox_list")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('desintox')")
     */
    public function listAction(Page $page)
    {
        return $this->render('desintox/list.html.twig', [
            'page' => $page,
            'clarifications' => $this->getDoctrine()->getRepository(Clarification::class)->findAll(),
        ]);
    }

    /**
     * @Route("/emmanuel-macron/desintox/{slug}", defaults={"_enable_campaign_silence"=true}, name="desintox_view")
     * @Method("GET")
     * @Entity("clarification", expr="repository.findPublishedClarification(slug)")
     */
    public function viewAction(Clarification $clarification)
    {
        return $this->render('desintox/view.html.twig', ['clarification' => $clarification]);
    }
}
