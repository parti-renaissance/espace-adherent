<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\MyCommittee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/espace-adherent/mon-comite-local', name: 'app_my_committee_show_current', methods: ['GET'])]
class ShowMyCurrentCommitteeController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('renaissance/adherent/my_committee/show_my_current_committee.html.twig');
    }
}
