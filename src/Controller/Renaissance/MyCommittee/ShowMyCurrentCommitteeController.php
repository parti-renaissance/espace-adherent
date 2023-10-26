<?php

namespace App\Controller\Renaissance\MyCommittee;

use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(path: '/espace-adherent/mon-comite-local', name: 'app_my_committee_show_current', methods: ['GET'])]
class ShowMyCurrentCommitteeController extends AbstractController
{
    public function __invoke(): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$adherent->isRenaissanceUser()) {
            return $this->redirect($this->generateUrl('app_renaissance_adherent_space', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        return $this->render('renaissance/adherent/my_committee/show_my_current_committee.html.twig');
    }
}
