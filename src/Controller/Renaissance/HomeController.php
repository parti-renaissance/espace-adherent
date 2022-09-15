<?php

namespace App\Controller\Renaissance;

use App\Form\Renaissance\Donation\DonationRequestAmountType;
use App\Form\Renaissance\NewsletterSubscriptionType;
use App\Repository\CommitmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="app_renaissance_homepage", methods={"GET"})
 */
class HomeController extends AbstractController
{
    public function __invoke(CommitmentRepository $commitmentRepository): Response
    {
        return $this->render('renaissance/home.html.twig', [
            'newsletter_form' => $this->createForm(NewsletterSubscriptionType::class, null, ['action' => $this->generateUrl('app_renaissance_newsletter_save')])->createView(),
            'donation_form' => $this->createForm(DonationRequestAmountType::class, null, ['action' => $this->generateUrl('app_renaissance_donation')])->createView(),
            'commitments' => $commitmentRepository->getAllOrdered(),
        ]);
    }
}
