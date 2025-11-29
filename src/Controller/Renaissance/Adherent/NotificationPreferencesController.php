<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adherent;

use App\Form\AdherentEmailSubscriptionType;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Subscription\SubscriptionHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(path: '/espace-adherent/preferences-des-emails', name: 'app_user_set_email_notifications', methods: ['GET', 'POST'])]
class NotificationPreferencesController extends AbstractController
{
    public function __invoke(
        Request $request,
        UserInterface $adherent,
        EventDispatcherInterface $dispatcher,
        SubscriptionHandler $subscriptionHandler,
    ): Response {
        $dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

        $form = $this
            ->createForm(AdherentEmailSubscriptionType::class, $adherent, [
                'is_adherent' => $adherent->isRenaissanceAdherent(),
                'validation_groups' => 'subscriptions_update',
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $subscriptionHandler->handleUpdateSubscription($adherent, []);

            $this->addFlash('info', 'adherent.set_emails_notifications.success');

            return $this->redirectToRoute('app_user_set_email_notifications');
        }

        return $this->render(
            'renaissance/adherent/email_notifications/form.html.twig',
            ['form' => $form->createView()]
        );
    }
}
