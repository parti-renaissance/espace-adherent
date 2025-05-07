<?php

namespace App\Controller\Api\Agora;

use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\AgoraMembership;
use App\Repository\AgoraMembershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class JoinAgoraController extends AbstractController
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

        if ($agoraMembership) {
            return $this->json([
                'status' => 'error',
                'message' => 'Vous êtes déjà membre de cette Agora.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($agora->isMembersFull()) {
            return $this->json([
                'status' => 'error',
                'message' => 'La limite de membres pour cette Agora a été atteinte.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $agoraMembership = new AgoraMembership();
        $agoraMembership->agora = $agora;
        $agoraMembership->adherent = $adherent;

        $manager->persist($agoraMembership);
        $manager->flush();

        return $this->json('OK', Response::HTTP_CREATED);
    }
}
