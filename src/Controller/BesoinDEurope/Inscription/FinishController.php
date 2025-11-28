<?php

declare(strict_types=1);

namespace App\Controller\BesoinDEurope\Inscription;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/inscription/felicitations', name: self::ROUTE_NAME, methods: ['GET'])]
class FinishController extends AbstractController
{
    public const ROUTE_NAME = 'app_bde_inscription_finish';

    public function __invoke(): Response
    {
        return $this->render('besoindeurope/inscription/finish.html.twig');
    }
}
