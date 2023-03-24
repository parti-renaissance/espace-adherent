<?php

namespace App\Controller\Api\ElectedRepresentative;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/v3/elected_representatives', name: 'api_elected_representatives_post_collection', methods: ['POST'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')")]
class CreateOrUpdateElectedRepresentativeController extends AbstractController
{
    public function __invoke(
        Request $request,
        ElectedRepresentativeRepository $electedRepresentativeRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $electedRepresentative = new ElectedRepresentative();

        $serializer->deserialize($request->getContent(), ElectedRepresentative::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $electedRepresentative,
            AbstractNormalizer::GROUPS => ['elected_representative_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        if ($adherent = $electedRepresentative->getAdherent()) {
            $existingElectedRepresentative = $electedRepresentativeRepository->findOneBy(['adherent' => $adherent]);

            if ($existingElectedRepresentative && empty($existingElectedRepresentative->getCurrentMandates())) {
                $electedRepresentative = $serializer->deserialize($request->getContent(), ElectedRepresentative::class, JsonEncoder::FORMAT, [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $existingElectedRepresentative,
                    AbstractNormalizer::GROUPS => ['elected_representative_write'],
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ]);
            }
        }

        $errors = $validator->validate($electedRepresentative);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if (!$electedRepresentative->getId()) {
            $entityManager->persist($electedRepresentative);
        }

        $entityManager->flush();

        return $this->json(
            $electedRepresentative,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['elected_representative_read']]
        );
    }
}
