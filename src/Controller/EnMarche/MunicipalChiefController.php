<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Intl\FranceCitiesBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-chef-municipal")
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF')")
 */
class MunicipalChiefController extends AbstractController
{
    /**
     * @Route("/", name="app_municipalchief_index")
     */
    public function indexAction(UserInterface $municipalChief): Response
    {
        return $this->render('municipal_chief/index.html.twig', [
            'managedCities' => FranceCitiesBundle::searchCitiesByInseeCodes(
                $municipalChief->getMunicipalChiefManagedArea()->getCodes()
            ),
        ]);
    }
}
