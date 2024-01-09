<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/rappel-communication', name: 'app_adhesion_communication_reminder', methods: ['GET', 'POST'])]
class CommunicationReminderController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->renderForm('renaissance/adhesion/communication_reminder.html.twig');
    }
}
