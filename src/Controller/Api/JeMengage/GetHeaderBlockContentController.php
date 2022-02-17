<?php

namespace App\Controller\Api\JeMengage;

use App\Entity\Adherent;
use App\Repository\JeMengage\HeaderBlockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

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
        ?UserInterface $user = null
    ): JsonResponse {
        if (!$headerBlock = $headerBlockRepository->findOneBySlug($slug)) {
            throw $this->createNotFoundException();
        }

        if ($content = $headerBlock->getContent()) {
            if ($user) {
                $content = str_replace('{{ prenom }}', $user->getFirstName(), $content);
            } else {
                $content = str_replace('{{ prenom }}', '', $content);
            }

            $content = str_replace('{{ date_echeance }}', $headerBlock->getDeadlineDate() ? $headerBlock->getDeadlineDate()->diff(new \DateTime())->days : 0, $content);
            $headerBlock->setContent($content);
        }

        return $this->json(
            $headerBlock,
            Response::HTTP_OK,
            [],
            ['groups' => ['header_block_read', 'image_owner_exposed']]
        );
    }
}
