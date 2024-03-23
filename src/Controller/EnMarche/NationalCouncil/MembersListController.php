<?php

namespace App\Controller\EnMarche\NationalCouncil;

use League\Flysystem\FilesystemOperator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/conseil-national/membres', name: 'app_national_council_members', methods: ['GET'])]
#[IsGranted('ROLE_NATIONAL_COUNCIL_MEMBER')]
class MembersListController extends AbstractController
{
    public function __invoke(FilesystemOperator $defaultStorage): Response
    {
        return new Response(
            $defaultStorage->read('/static/instances/conseil-national/d47e87f8-a3a1-403f-9ed9-8382053ba3fb.pdf'),
            Response::HTTP_OK,
            ['Content-type' => 'application/pdf']
        );
    }
}
