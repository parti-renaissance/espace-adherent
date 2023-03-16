<?php

namespace App\Controller\EnMarche\NationalCouncil;

use App\Repository\Instance\NationalCouncil\ElectionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_NATIONAL_COUNCIL_MEMBER")
 */
#[Route(path: '/conseil-national', name: 'app_national_council_index', methods: ['GET'])]
class IndexController extends AbstractController
{
    public function __invoke(ElectionRepository $electionRepository): Response
    {
        return $this->render('national_council/index.html.twig', [
            'election' => $electionRepository->findLast(),
        ]);
    }
}
