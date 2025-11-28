<?php

declare(strict_types=1);

namespace App\Controller\EnMarche;

use App\Entity\SocialShare;
use App\Entity\SocialShareCategory;
use App\Repository\SocialShareCategoryRepository;
use App\Repository\SocialShareRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SocialShareController extends AbstractController
{
    #[Route(path: '/jepartage', name: 'app_social_share_list', methods: ['GET'])]
    public function listAction(EntityManagerInterface $manager): Response
    {
        return $this->render('social_share/wall.html.twig', [
            'currentCategory' => null,
            'socialShareCategories' => $manager->getRepository(SocialShareCategory::class)->findForWall(),
            'socialShares' => $manager->getRepository(SocialShare::class)->findForWall(),
        ]);
    }

    #[Route(path: '/jepartage/{slug}', name: 'app_social_share_show', methods: ['GET'])]
    public function showAction(
        SocialShareCategory $category,
        SocialShareCategoryRepository $socialShareCategoryRepository,
        SocialShareRepository $socialShareRepository,
    ): Response {
        return $this->render('social_share/wall.html.twig', [
            'currentCategory' => $category,
            'socialShareCategories' => $socialShareCategoryRepository->findForWall(),
            'socialShares' => $socialShareRepository->findForWall($category),
        ]);
    }

    #[Route(path: '/je-partage', methods: ['GET'])]
    public function redirectAction(): Response
    {
        return $this->redirectToRoute('app_social_share_list', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
