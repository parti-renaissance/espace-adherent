<?php

namespace App\AdherentMessage\Filter;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\CoalitionsFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\JecouteFilter;
use App\Entity\AdherentMessage\Filter\LreManagerElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\Filter\ReferentInstancesFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\Audience\AudienceInterface;

abstract class FilterFactory
{
    public static function create(Adherent $user, string $messageType): AdherentMessageFilterInterface
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::REFERENT:
                return static::createReferentFilter($user);
            case AdherentMessageTypeEnum::DEPUTY:
                return static::createDeputyFilter($user);
            case AdherentMessageTypeEnum::SENATOR:
                return static::createSenatorFilter($user);
            case AdherentMessageTypeEnum::COMMITTEE:
                return static::createCommitteeFilter();
            case AdherentMessageTypeEnum::MUNICIPAL_CHIEF:
                return static::createMunicipalChiefFilter($user);
            case AdherentMessageTypeEnum::REFERENT_ELECTED_REPRESENTATIVE:
                return static::createReferentElectedRepresentativeFilter($user);
            case AdherentMessageTypeEnum::LRE_MANAGER_ELECTED_REPRESENTATIVE:
                return static::createLreManagerElectedRepresentativeFilter($user);
            case AdherentMessageTypeEnum::REFERENT_INSTANCES:
                return static::createReferentTerritorialCouncilFilter($user);
            case AdherentMessageTypeEnum::LEGISLATIVE_CANDIDATE:
                return static::createLegislativeCandidateFilter($user);
            case AdherentMessageTypeEnum::CANDIDATE:
                return static::createCandidateFilter($user);
            case AdherentMessageTypeEnum::CANDIDATE_JECOUTE:
                return static::createCandidateJecouteFilter($user);
            case AdherentMessageTypeEnum::COALITIONS:
                return static::createCoalitionsFilter($user);
        }

        throw new \InvalidArgumentException(sprintf('Invalid message type "%s"', $messageType));
    }

    public static function createFromAudience(AudienceInterface $audience, string $type): AdherentMessageFilterInterface
    {
        $filter = new AudienceFilter();
        if (AdherentMessageTypeEnum::CANDIDATE === $type) {
            $filter->setZone($audience->getZone());
        } else {
            $filter->setReferentTags($audience->getZone()->getReferentTags()->toArray());
        }
        $filter->setGender($audience->getGender());
        $filter->setFirstName($audience->getFirstName());
        $filter->setLastName($audience->getLastName());
        $filter->setAgeMin($audience->getAgeMin());
        $filter->setAgeMax($audience->getAgeMax());
        $filter->setRegisteredSince($audience->getRegisteredSince());
        $filter->setRegisteredUntil($audience->getRegisteredUntil());
        $filter->setIsCertified($audience->isCertified());
        if (null !== $audience->isCommitteeMember()) {
            $filter->setIncludeAdherentsInCommittee($audience->isCommitteeMember());
            $filter->setIncludeAdherentsNoCommittee(!$audience->isCommitteeMember());
        }

        return $filter;
    }

    private static function createReferentFilter(Adherent $user): ReferentUserFilter
    {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        return new ReferentUserFilter($managedArea->getTags()->toArray());
    }

    private static function createDeputyFilter(Adherent $user): AdherentZoneFilter
    {
        if (!$user->isDeputy()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a deputy');
        }

        return new AdherentZoneFilter($user->getManagedDistrict()->getReferentTag());
    }

    private static function createCommitteeFilter(): CommitteeFilter
    {
        return new CommitteeFilter();
    }

    private static function createMunicipalChiefFilter(Adherent $adherent): MunicipalChiefFilter
    {
        return new MunicipalChiefFilter($adherent->getMunicipalChiefManagedArea()->getInseeCode());
    }

    private static function createSenatorFilter(Adherent $user): AdherentZoneFilter
    {
        return new AdherentZoneFilter($user->getSenatorArea()->getDepartmentTag());
    }

    private static function createReferentElectedRepresentativeFilter(
        Adherent $user
    ): ReferentElectedRepresentativeFilter {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        return new ReferentElectedRepresentativeFilter($managedArea->getTags()->first());
    }

    private static function createLreManagerElectedRepresentativeFilter(
        Adherent $user
    ): LreManagerElectedRepresentativeFilter {
        $lreArea = $user->getLreArea();

        if (!$lreArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a LRE Manager', $user->getEmailAddress()));
        }

        return new LreManagerElectedRepresentativeFilter($lreArea->getReferentTag());
    }

    private static function createReferentTerritorialCouncilFilter(Adherent $user): ReferentInstancesFilter
    {
        $managedArea = $user->getManagedArea();

        if (!$managedArea) {
            throw new \InvalidArgumentException(sprintf('[AdherentMessage] The user "%s" is not a referent', $user->getEmailAddress()));
        }

        return new ReferentInstancesFilter();
    }

    private static function createLegislativeCandidateFilter(Adherent $user): AdherentZoneFilter
    {
        if (!$user->isLegislativeCandidate()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a legislative candidate');
        }

        return new AdherentZoneFilter($user->getLegislativeCandidateManagedDistrict()->getReferentTag());
    }

    private static function createCandidateFilter(Adherent $user): AdherentGeoZoneFilter
    {
        if (!$user->isCandidate()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a candidate');
        }

        return new AdherentGeoZoneFilter($user->getCandidateManagedArea()->getZone());
    }

    private static function createCandidateJecouteFilter(Adherent $user): JecouteFilter
    {
        if (!$user->isCandidate()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a candidate');
        }

        return new JecouteFilter($user->getCandidateManagedArea()->getZone());
    }

    private static function createCoalitionsFilter(Adherent $user): CoalitionsFilter
    {
        if (!$user->isApprovedCauseAuthor()) {
            throw new \InvalidArgumentException('[AdherentMessage] Adherent should be a cause author');
        }

        return new CoalitionsFilter();
    }
}
