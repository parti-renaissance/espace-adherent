<?php

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/test/ux-component/{component}', requirements: ['component' => '[a-zA-Z:]+'], methods: ['GET'])]
class TestUXComponentController extends AbstractController
{
    public function __invoke(Request $request, string $component): Response
    {
        return $this->render('ux_component.html.twig', [
            'component' => $component,
            'component_attributes' => $request->query->all(),
        ]);
    }
}
