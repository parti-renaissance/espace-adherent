<?php

namespace App\Controller\EnMarche\Jecoute\News;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-referent/actualites", name="app_jecoute_news_referent_")
 *
 * @Security("is_granted('ROLE_JECOUTE_NEWS') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE_NEWS'))")
 */
class NewsReferentController extends AbstractNewsController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::REFERENT_SPACE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray());
    }
}
