<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/evenements")
 */
class EventsController extends Controller
{
    /**
     * @Route(name="events_index")
     */
    public function indexAction()
    {
        return $this->render('events/index.html.twig');
    }
}
