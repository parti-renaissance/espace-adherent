<?php

namespace AppBundle\Controller\EnMarche\Election;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_MUNICIPAL_MANAGER')")
 */
class MunicipalManagerCityController extends Controller
{
    /**
     * @Route("/espace-responsable-communal/assesseurs/communes", name="app_municipal_manager_cities_list")
     */
    public function __invoke(): Response
    {
        return $this->render('municipal_manager/cities.html.twig');
    }
}
