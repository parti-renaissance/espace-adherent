<?php

declare(strict_types=1);

namespace App\Controller\Api\Vox;

use App\Entity\Adherent;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\JeMengage\HeaderBlockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/je-mengage/headers/{slug}', name: 'api_get_jemengage_header_blocks', methods: ['GET'])]
#[Route(path: '/v3/je-mengage/headers/{slug}', name: 'api_get_v3_jemengage_header_blocks', methods: ['GET'])]
class GetHeaderBlockContentController extends AbstractController
{
    public function __invoke(
        string $slug,
        HeaderBlockRepository $headerBlockRepository,
        NormalizerInterface $normalizer,
    ): JsonResponse {
        /** @var Adherent $user */
        $user = $this->getUser();

        if (!$headerBlock = $headerBlockRepository->findOneBySlug($slug)) {
            throw $this->createNotFoundException();
        }

        $data = $normalizer->normalize($headerBlock, 'array', ['groups' => ['header_block_read', ImageExposeNormalizer::NORMALIZATION_GROUP]]);

        if (isset($data['content']) && $content = $data['content']) {
            if ($user) {
                $content = str_replace('{{ prenom }}', $user->getFirstName(), $content);
            } else {
                $content = str_replace('{{ prenom }}', '', $content);
            }

            $content = str_replace('{{ date_echeance }}', (string) ($headerBlock->getDeadlineDate() ? $headerBlock->getDeadlineDate()->diff(new \DateTime())->days : 0), $content);
            $data['content'] = $content;
        }

        return $this->json($data);
    }
}
