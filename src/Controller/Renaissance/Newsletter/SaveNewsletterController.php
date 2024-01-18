<?php

namespace App\Controller\Renaissance\Newsletter;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Form\Renaissance\NewsletterSubscriptionType;
use App\Renaissance\Newsletter\Command\SendWelcomeMailCommand;
use App\Renaissance\Newsletter\SubscriptionRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/newsletter', name: 'app_renaissance_newsletter_save', methods: ['POST'])]
class SaveNewsletterController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subscription = SubscriptionRequest::createFromRecaptcha($request->request->get('frc-captcha-solution'));

        $form = $this
            ->createForm(NewsletterSubscriptionType::class, $subscription)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($newsletterSubscription = NewsletterSubscription::create($subscription));
            $entityManager->flush();

            $this->dispatchMessage(new SendWelcomeMailCommand($newsletterSubscription));

            $this->addFlash('success', 'Merci pour votre inscription ! Nous vous invitons à la valider en cliquant sur le lien reçu par email.');

            return $this->redirectToRoute('renaissance_site');
        }

        $errors = $form->getErrors(true);

        if ($errors->count()) {
            $this->addFlash('newsletter_error', $errors->current()->getMessage());
        }

        return $this->redirectToRoute('app_renaissance_homepage', ['_fragment' => 'newsletter-form-error']);
    }
}
