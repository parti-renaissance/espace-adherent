<?php

namespace App\Jecoute;

use App\Entity\Jecoute\News;

class NewsTitlePrefix
{
    public function prefixTitle(News $news): string
    {
        $author = $news->getAuthor();
        $title = $news->getTitle();
        if (null === $news->getCreatedBy() && null !== $author) {
            if ($author->isCandidate()) {
                $title = sprintf('[Candidat] %s', $news->getTitle());
            }

            if ($author->isReferent()) {
                $title = sprintf('[Référent] %s', $news->getTitle());
            }
        }

        return $title;
    }
}
