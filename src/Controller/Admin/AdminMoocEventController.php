<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\MoocEvent;
use AppBundle\MoocEvent\MoocEventManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/evenement-mooc")
 * @Security("has_role('ROLE_ADMIN_MOOC_EVENTS')")
 */
class AdminMoocEventController extends Controller
{
    /**
     * @Route("/{uuid}/publish", name="app_admin_mooc_event_publish")
     * @Method("GET")
     */
    public function publishAction(Request $request, MoocEvent $moocEvent): Response
    {
        $this->get(MoocEventManager::class)->publishMoocEvent($moocEvent);
        $this->get('sonata.core.flashmessage.manager')->getSession()->getFlashBag()->set('success', sprintf('L\'élément "%s" a été mis à jour avec succès.', $moocEvent->getName()));

        return $this->redirect($request->headers->get('referer'));
    }
}
