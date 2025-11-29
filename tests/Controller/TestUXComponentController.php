<?php

declare(strict_types=1);

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/test/ux-component/{component}/{template}', requirements: ['component' => '[a-zA-Z:]+', 'template' => '[a-zA-Z:]+'], methods: ['GET'])]
class TestUXComponentController extends AbstractController
{
    public function __invoke(Request $request, string $component, string $template): Response
    {
        return $this->render('ux_component.html.twig', [
            'partial_path' => "test_ux_components/$component/_$template.html.twig",
            'component_attributes' => $request->query->all(),
        ]);
    }
}
