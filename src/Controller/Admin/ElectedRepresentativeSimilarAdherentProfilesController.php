<?php

namespace App\Controller\Admin;

use App\ElectedRepresentative\ElectedRepresentativeEvent;
use App\ElectedRepresentative\ElectedRepresentativeEvents;
use App\Entity\Adherent;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ElectedRepresentativeSimilarAdherentProfilesController extends AbstractController
{
    #[Route(path: '/elected-representative/{id}/adherent-similar-profiles', name: 'admin_app_electedrepresentative_adherent_similar_profiles', methods: ['GET'])]
    public function showSimilarProfilesAction(
        ElectedRepresentative $electedRepresentative,
        AdherentRepository $adherentRepository,
    ) {
        return $this->render('admin/elected_representative/adherent_similar_profiles.html.twig', [
            'elected_representative' => $electedRepresentative,
            'similar_profiles' => $adherentRepository->findSimilarProfilesForElectedRepresentative($electedRepresentative),
        ]);
    }

    #[ParamConverter('adherent', options: ['mapping' => ['adherent_id' => 'id']])]
    #[Route(path: '/elected-representative/{id}/adherent-similar-profiles/{adherent_id}/link', name: 'admin_app_electedrepresentative_adherent_similar_profiles_link', methods: ['GET'])]
    public function linkAdherentToElectedRepresentativeAction(
        ElectedRepresentative $electedRepresentative,
        Adherent $adherent,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
    ) {
        $dispatcher->dispatch(new ElectedRepresentativeEvent($electedRepresentative), ElectedRepresentativeEvents::BEFORE_UPDATE);

        $electedRepresentative->setAdherent($adherent);
        $entityManager->flush();

        $dispatcher->dispatch(new ElectedRepresentativeEvent($electedRepresentative), ElectedRepresentativeEvents::POST_UPDATE);

        $this->addFlash('success', 'Le profil adhérent a bien été lié à l\'élu(e).');

        return $this->redirectToRoute('admin_app_electedrepresentative_electedrepresentative_edit', ['id' => $electedRepresentative->getId()]);
    }
}
