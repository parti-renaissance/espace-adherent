<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Clarification;
use AppBundle\Entity\Page;
use AppBundle\Repository\ClarificationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DesintoxController extends Controller
{
    /**
     * @Route("/desintox", defaults={"_enable_campaign_silence"=true}, name="desintox_list")
     * @Method("GET")
     * @Entity("page", expr="repository.findOneBySlug('desintox')")
     */
    public function listAction(Page $page, ClarificationRepository $clarificationRepository)
    {
        return $this->render('desintox/list.html.twig', [
            'page' => $page,
            'clarifications' => $clarificationRepository->findAllPublished(),
        ]);
    }

    /**
     * @Route("/desintox/{slug}", defaults={"_enable_campaign_silence"=true}, name="desintox_view")
     * @Method("GET")
     * @Entity("clarification", expr="repository.findPublishedClarification(slug)")
     */
    public function viewAction(Clarification $clarification)
    {
        return $this->render('desintox/view.html.twig', ['clarification' => $clarification]);
    }
}
