<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\DepartmentSite;

use App\Entity\DepartmentSite\DepartmentSite;
use App\Repository\Geo\ZoneRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/federations', name: 'app_renaissance_department_site_')]
class DepartmentSiteController extends AbstractController
{
    #[Route(name: 'list')]
    public function departmentSiteListAction(ZoneRepository $zoneRepository): Response
    {
        return $this->render('renaissance/department_site/list.html.twig', [
            'department_sites' => $zoneRepository->findAllDepartmentSiteIndexByCode(),
        ]);
    }

    #[Route(path: '/{slug}', name: 'view')]
    public function departmentSiteAction(
        #[MapEntity(expr: 'repository.findOneBySlug(slug)')]
        DepartmentSite $departmentSite,
    ): Response {
        return $this->render('renaissance/department_site/department_site.html.twig', [
            'department_site' => $departmentSite,
        ]);
    }
}
