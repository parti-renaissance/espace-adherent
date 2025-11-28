<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Donation;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN_FINANCE')]
#[Route(path: '/donation')]
class AdminDonationController extends AbstractController
{
    #[Route(path: '/file/{id}', name: 'app_admin_donation_file', methods: 'GET')]
    public function fileAction(Donation $donation, FilesystemOperator $defaultStorage): Response
    {
        $filePath = $donation->getFilePathWithDirectory();

        if (!$defaultStorage->has($filePath)) {
            throw $this->createNotFoundException();
        }

        return new Response($defaultStorage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $defaultStorage->mimeType($filePath),
        ]);
    }

    #[Route(path: '/refund/{id}', name: 'app_admin_donation_refund', methods: 'GET')]
    public function refundAction(Donation $donation, EntityManagerInterface $em): Response
    {
        $donation->markAsRefunded();

        $em->flush();

        return $this->redirectToRoute('admin_app_donation_edit', ['id' => $donation->getId()]);
    }
}
