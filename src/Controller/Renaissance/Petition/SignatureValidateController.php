<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Petition;

use App\Entity\PetitionSignature;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/petition/validate/{uuid}/{token}', name: 'app_petition_validate', methods: ['GET'])]
class SignatureValidateController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $secret,
    ) {
    }

    public function __invoke(PetitionSignature $signature, string $token): Response
    {
        $thanksUrl = \sprintf(
            '%s/petitions/%s?merci',
            rtrim($this->generateUrl('renaissance_site'), '/'),
            $signature->petitionSlug
        );

        if ($signature->validatedAt) {
            return $this->redirect($thanksUrl);
        }

        if (
            !($uuidFromToken = JWT::decode($token, new Key($this->secret, 'HS256'))?->uuid)
            || $uuidFromToken !== $signature->getUuid()->toString()
        ) {
            $this->addFlash('error', 'Le lien de confirmation est invalide');

            return $this->render('renaissance/petition/confirmation_error.html.twig');
        }

        $signature->validate();
        $this->entityManager->flush();

        return $this->redirect($thanksUrl);
    }
}
