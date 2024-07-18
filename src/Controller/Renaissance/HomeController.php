<?php

namespace App\Controller\Renaissance;

use App\Form\Renaissance\NewsletterSubscriptionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/', name: 'app_renaissance_homepage', methods: ['GET'])]
class HomeController extends AbstractController
{
    public function __invoke(
    ): Response {
        return $this->render('renaissance/home.html.twig', [
            'newsletter_form' => $this->createForm(NewsletterSubscriptionType::class, null, ['action' => $this->generateUrl('app_renaissance_newsletter_save')])->createView(),
        ]);
    }
}
