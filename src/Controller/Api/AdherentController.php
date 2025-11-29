<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Security\Voter\ManagedUserVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdherentController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route(path: '/adherents/me/anonymize', name: 'api_adherent_anonymize_me', methods: ['PUT'])]
    public function anonymizeAction(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $manager,
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$nickname = $data['nickname'] ?? null) {
            return new JsonResponse('Property "nickname" is required.', Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $adherent->setNickname($nickname);
        $adherent->setNicknameUsed($data['use_nickname'] ?? false);

        $violations = $validator->validate($adherent, null, ['anonymize']);

        if (0 === $violations->count()) {
            $manager->flush();

            return $this->json('OK');
        }

        $errors = $serializer->serialize($violations, 'jsonproblem');

        return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
    }

    #[Route(path: '/adherents/{uuid}/committees', name: 'api_adherent_committees', methods: ['GET'])]
    public function getAdherentCommittees(Adherent $adherent): Response
    {
        $this->denyAccessUnlessGranted(ManagedUserVoter::IS_MANAGED_USER, $adherent);

        return $this->json(
            array_filter([$adherent->getCommitteeMembership()]),
            Response::HTTP_OK,
            [],
            ['groups' => ['adherent_committees_modal']]
        );
    }
}
