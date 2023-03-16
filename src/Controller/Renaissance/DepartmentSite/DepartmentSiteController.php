<?php

namespace App\Controller\Renaissance\DepartmentSite;

use App\Entity\DepartmentSite\DepartmentSite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/federations', name: 'app_renaissance_department_site_')]
class DepartmentSiteController extends AbstractController
{
    #[Route(path: '/{slug}', name: 'view')]
    #[Entity('departmentSite', expr: 'repository.findOneBySlug(slug)')]
    public function departmentSiteAction(DepartmentSite $departmentSite): Response
    {
        return $this->render('renaissance/department_site/department_site.html.twig', [
            'department_site' => $departmentSite,
        ]);
    }
}
