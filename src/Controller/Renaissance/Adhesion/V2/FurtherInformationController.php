<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v2/adhesion/informations-complementaires', name: 'app_adhesion_further_information', methods: ['GET', 'POST'])]
class FurtherInformationController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        return $this->render('renaissance/adhesion/further_information.html.twig');
    }
}
