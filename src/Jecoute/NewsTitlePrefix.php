<?php

namespace App\Jecoute;

use App\Entity\Jecoute\News;

class NewsTitlePrefix
{
    public const PREFIX_CANDIDATE_SPACE = 'Régionales';
    public const PREFIX_REFERENT_SPACE = 'Référent';

    public function prefixTitle(News $news): string
    {
        $space = $news->getSpace();
        switch ($space) {
            case JecouteSpaceEnum::CANDIDATE_SPACE:
                return \sprintf('[%s] %s', self::PREFIX_CANDIDATE_SPACE, $news->getTitle());
            case JecouteSpaceEnum::REFERENT_SPACE:
                return \sprintf('[%s] %s', self::PREFIX_REFERENT_SPACE, $news->getTitle());
            default:
                return $news->getTitle();
        }
    }

    public function removePrefix(News $news): void
    {
        $space = $news->getSpace();
        $prefix = null;
        switch ($space) {
            case JecouteSpaceEnum::CANDIDATE_SPACE:
                $prefix = \sprintf('[%s] ', self::PREFIX_CANDIDATE_SPACE);

                break;
            case JecouteSpaceEnum::REFERENT_SPACE:
                $prefix = \sprintf('[%s] ', self::PREFIX_REFERENT_SPACE);

                break;
            default:
                $prefix = null;
        }

        if ($prefix && 0 === mb_strpos($news->getTitle(), $prefix)) {
            $news->setTitle(mb_substr($news->getTitle(), mb_strlen($prefix)));
        }
    }
}
