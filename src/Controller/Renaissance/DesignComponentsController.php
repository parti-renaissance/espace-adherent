<?php

declare(strict_types=1);

namespace App\Controller\Renaissance;

use App\Controller\CanaryControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/design-components', name: 'app_design_components')]
class DesignComponentsController extends AbstractController
{
    use CanaryControllerTrait;

    public function __invoke(): Response
    {
        $this->disableInProduction();

        return $this->render('renaissance/design_components.html.twig');
    }
}
