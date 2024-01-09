<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/carte-adherent', name: 'app_adhesion_member_card', methods: ['GET', 'POST'])]
class MemberCardController extends AbstractController
{
    public function __invoke(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        return $this->renderForm('renaissance/adhesion/member_card.html.twig');
    }
}
