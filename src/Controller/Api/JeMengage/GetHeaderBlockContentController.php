<?php

namespace App\Controller\Api\JeMengage;

use App\Entity\Adherent;
use App\Repository\JeMengage\HeaderBlockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/je-mengage/headers/{slug}", name="api_get_jemengage_header_blocks", methods={"GET"})
 * @Route("/v3/je-mengage/headers/{slug}", name="api_get_v3_jemengage_header_blocks", methods={"GET"})
 */
class GetHeaderBlockContentController extends AbstractController
{
    /**
     * @param Adherent $user
     */
    public function __invoke(
        string $slug,
        HeaderBlockRepository $headerBlockRepository,
        NormalizerInterface $normalizer,
        ?UserInterface $user = null
    ): JsonResponse {
        if (!$headerBlock = $headerBlockRepository->findOneBySlug($slug)) {
            throw $this->createNotFoundException();
        }

        $data = $normalizer->normalize($headerBlock, 'array', ['groups' => ['header_block_read', 'image_owner_exposed']]);

        if (isset($data['content']) && $content = $data['content']) {
            if ($user) {
                $content = str_replace('{{ prenom }}', $user->getFirstName(), $content);
            } else {
                $content = str_replace('{{ prenom }}', '', $content);
            }

            $content = str_replace('{{ date_echeance }}', $headerBlock->getDeadlineDate() ? $headerBlock->getDeadlineDate()->diff(new \DateTime())->days : 0, $content);
            $data['content'] = $content;
        }

        return $this->json($data);
    }
}
