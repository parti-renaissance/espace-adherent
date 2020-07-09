<?php

namespace App\Controller\EnMarche\CitizenProjects;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Referent\ManagedCitizenProjectsExporter;
use App\Repository\CitizenProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractCitizenProjectsController extends Controller
{
    use AccessDelegatorTrait;

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
            'managedGroupsJson' => $citizenProjectsExporter->exportAsJson($citizenProjectRepository->findManagedByReferent($this->getMainUser($request->getSession()))),
            'base_template' => sprintf('citizen_projects/_base_%s.html.twig', $spaceName = $this->getSpaceType()),
            'space_name' => $spaceName,
        ]);
    }

    abstract protected function getSpaceType(): string;
}
