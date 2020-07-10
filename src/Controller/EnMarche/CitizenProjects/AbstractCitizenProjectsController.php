<?php

namespace App\Controller\EnMarche\CitizenProjects;

use App\Referent\ManagedCitizenProjectsExporter;
use App\Repository\CitizenProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractCitizenProjectsController extends Controller
{
    /**
     * @Route("", name="list")
     */
    public function citizenProjectsAction(
        Request $request,
        CitizenProjectRepository $citizenProjectRepository,
        ManagedCitizenProjectsExporter $citizenProjectsExporter
    ): Response {
        return $this->render('referent/base_group_list.html.twig', [
            'title' => 'Projets citoyens',
            'managedGroupsJson' => $citizenProjectsExporter->exportAsJson($citizenProjectRepository->findManagedByReferent($this->getMainUser($request))),
            'base_template' => sprintf('citizen_projects/_base_%s.html.twig', $spaceName = $this->getSpaceType()),
            'space_name' => $spaceName,
        ]);
    }

    abstract protected function getSpaceType(): string;

    abstract protected function getMainUser(Request $request): UserInterface;
}
