<?php

namespace App\Controller\Renaissance;

use App\Repository\CommitmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="app_renaissance_homepage", methods={"GET"})
 */
class HomeController extends AbstractController
{
    public function __invoke(CommitmentRepository $commitmentRepository): Response
    {
        return $this->render('renaissance/home.html.twig', ['commitments' => $commitmentRepository->getAllOrdered()]);
    }
}
