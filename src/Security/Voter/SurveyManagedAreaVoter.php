<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Repository\Jecoute\LocalSurveyRepository;

class SurveyManagedAreaVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_SURVEY_MANAGER_OF';

    private $localSurveyRepository;

    public function __construct(LocalSurveyRepository $localSurveyRepository)
    {
        $this->localSurveyRepository = $localSurveyRepository;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $tags = $adherent->isJecouteManager() ? $adherent->getJecouteManagedArea()->getCodes() :
            $adherent->getManagedArea()->getReferentTagCodes()
        ;

        /** @var LocalSurvey $subject */
        return !empty(array_intersect($subject->getTags(), $tags));
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof LocalSurvey;
    }
}
