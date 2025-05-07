<?php

namespace App\Controller\Api\Agora;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Repository\AgoraMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class LeaveAgoraController extends AbstractController
{
    public function __invoke(
        Agora $agora,
        #[CurrentUser] Adherent $adherent,
        AgoraMembershipRepository $agoraMembershipRepository,
        EntityManagerInterface $manager,
    ): Response {
        if (!$agora->published) {
            throw $this->createNotFoundException('Agora not found');
        }

        $agoraMembership = $agoraMembershipRepository->findMembership($agora, $adherent);

        if (!$agoraMembership) {
            return $this->json([
                'status' => 'error',
                'message' => 'Vous n\'êtes pas membre de cette Agora.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $manager->remove($agoraMembership);
        $manager->flush();

        return $this->json('OK', Response::HTTP_OK);
    }
}
