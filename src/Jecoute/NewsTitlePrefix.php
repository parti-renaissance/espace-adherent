<?php

namespace App\Jecoute;

use App\Entity\Jecoute\News;

class NewsTitlePrefix
{
    public function prefixTitle(News $news): string
    {
        $space = $news->getSpace();
        switch ($space) {
            case JecouteSpaceEnum::CANDIDATE_SPACE:
                return sprintf('[Régionales] %s', $news->getTitle());
            case JecouteSpaceEnum::REFERENT_SPACE:
                return sprintf('[Référent] %s', $news->getTitle());
            default:
                return $news->getTitle();
        }
    }
}
