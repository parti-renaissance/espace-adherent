<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/choisir-mon-comite', name: 'app_adhesion_committee', methods: ['GET', 'POST'])]
class CommitteeController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->renderForm('renaissance/adhesion/committee.html.twig');
    }
}
