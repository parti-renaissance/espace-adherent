<?php

namespace App\Controller\Api\Article;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RetrieveArticleController
{
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    public function __invoke(string $id): ?Article
    {
        if (!$data = $this->articleRepository->findOneBySlug($id)) {
            throw new NotFoundHttpException(sprintf('Article with slug %s not found.', $id));
        }

        return $data;
    }
}
