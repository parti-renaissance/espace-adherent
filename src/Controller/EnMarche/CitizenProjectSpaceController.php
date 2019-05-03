<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\CitizenProjectRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-projet-citoyen", name="app_citizen_project_space_dashboard")
 *
 * @Security("is_granted('ROLE_CITIZEN_PROJECT_ADMINISTRATOR')")
 */
class CitizenProjectSpaceController extends AbstractController
{
    /**
     * @param UserInterface|Adherent $adherent
     */
    public function __invoke(UserInterface $adherent, CitizenProjectRepository $repository): Response
    {
        return $this->render('citizen_project/dashboard.html.twig', [
            'citizen_projects' => $repository->findAllRegisteredCitizenProjectsForAdherent($adherent, true),
        ]);
    }
}
