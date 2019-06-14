<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-chef-municipal")
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route(name="app_municipalchief_index")
     */
    public function indexAction(): Response
    {
        $this->disableInProduction();

        return $this->render('municipal_chief/index.html.twig');
    }
}
