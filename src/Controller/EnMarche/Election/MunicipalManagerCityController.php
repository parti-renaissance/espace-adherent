<?php

namespace App\Controller\EnMarche\Election;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_MUNICIPAL_MANAGER")
 */
class MunicipalManagerCityController extends AbstractController
{
    /**
     * @Route("/espace-responsable-communal/assesseurs/communes", name="app_municipal_manager_cities_list")
     */
    public function __invoke(): Response
    {
        return $this->render('municipal_manager/cities.html.twig');
    }
}
