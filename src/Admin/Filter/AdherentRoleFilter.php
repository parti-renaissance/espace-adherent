<?php

namespace App\Admin\Filter;

use App\Adherent\AdherentRoleEnum;
use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccessEnum;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdherentRoleFilter extends AbstractCallbackDecoratorFilter
{
    protected function getInitialFilterOptions(): array
    {
        return [
            'field_type' => ChoiceType::class,
            'show_filter' => true,
            'field_options' => [
                'choices' => array_merge(AdherentRoleEnum::toArray(), ZoneBasedRoleTypeEnum::ALL),
                'choice_label' => function (string $value) {
                    return 'role.'.$value;
                },
                'multiple' => true,
            ],
            'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                if (!$value->hasValue()) {
                    return false;
                }

                $value = $value->getValue();

                /** @var QueryBuilder $qb */
                $where = new Expr\Orx();

                // Referent
                if (\in_array(AdherentRoleEnum::REFERENT, $value, true)) {
                    $where->add(sprintf('%s.managedArea IS NOT NULL', $alias));
                }

                // Co-Referent
                if (\in_array(AdherentRoleEnum::COREFERENT, $value, true)) {
                    $qb->leftJoin(sprintf('%s.referentTeamMember', $alias), 'rtm');
                    $where->add('rtm.id IS NOT NULL');
                }

                // Committee supervisor
                if ($committeeMandates = array_intersect([AdherentRoleEnum::COMMITTEE_SUPERVISOR, AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR], $value)) {
                    $qb->leftJoin(sprintf('%s.adherentMandates', $alias), 'am');
                    $condition = '';
                    if (1 === \count($committeeMandates)) {
                        $condition = ' AND am.provisional = :provisional';
                        $qb->setParameter('provisional', \in_array(AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR, $value, true));
                    }
                    $where->add('am.quality = :supervisor AND am.committee IS NOT NULL AND am.finishAt IS NULL'.$condition);

                    $qb->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR);
                }

                // Committee animator
                if (\in_array(AdherentRoleEnum::ANIMATOR, $value, true)) {
                    $qb->leftJoin(sprintf('%s.animatorCommittees', $alias), 'ac');
                    $where->add('ac IS NOT NULL');
                }

                // Committee host
                if (\in_array(AdherentRoleEnum::COMMITTEE_HOST, $value, true)) {
                    $qb->leftJoin(sprintf('%s.memberships', $alias), 'ms');
                    $where->add('ms.privilege = :committee_privilege');
                    $qb->setParameter('committee_privilege', CommitteeMembership::COMMITTEE_HOST);
                }

                // Senator
                if (\in_array(AdherentRoleEnum::SENATOR, $value, true)) {
                    $qb->leftJoin(sprintf('%s.senatorArea', $alias), 'senatorArea');
                    $where->add('senatorArea IS NOT NULL');
                }

                // Board Member
                if (\in_array(AdherentRoleEnum::BOARD_MEMBER, $value, true)) {
                    $qb->leftJoin(sprintf('%s.boardMember', $alias), 'boardMember');
                    $where->add('boardMember IS NOT NULL');
                }

                // Assessor Manager
                if (\in_array(AdherentRoleEnum::ASSESSOR_MANAGER, $value, true)) {
                    $qb->leftJoin(sprintf('%s.assessorManagedArea', $alias), 'assessorManagedArea');
                    $where->add('assessorManagedArea IS NOT NULL AND assessorManagedArea.codes IS NOT NULL');
                }

                // Assessor
                if (\in_array(AdherentRoleEnum::ASSESSOR, $value, true)) {
                    $qb->leftJoin(sprintf('%s.assessorRole', $alias), 'assessorRole');
                    $where->add('assessorRole IS NOT NULL AND assessorRole.votePlace IS NOT NULL');
                }

                // Election results reporter
                if (\in_array(AdherentRoleEnum::ELECTION_RESULTS_REPORTER, $value, true)) {
                    $where->add(sprintf('%s.electionResultsReporter = :election_result_reporter', $alias));
                    $qb->setParameter('election_result_reporter', true);
                }

                // J'écoute Manager
                if (\in_array(AdherentRoleEnum::JECOUTE_MANAGER, $value, true)) {
                    $qb->leftJoin(sprintf('%s.jecouteManagedArea', $alias), 'jecouteManagedArea');
                    $where->add('jecouteManagedArea IS NOT NULL AND jecouteManagedArea.zone IS NOT NULL');
                }

                // User
                if (\in_array(AdherentRoleEnum::USER, $value, true)) {
                    $where->add(sprintf('%s.adherent = 0', $alias));
                }

                // National Role
                if (\in_array(AdherentRoleEnum::NATIONAL, $value, true)) {
                    $where->add("$alias.nationalRole = :nationalRole");
                    $qb->setParameter('nationalRole', true);
                }

                // National Communication Role
                if (\in_array(AdherentRoleEnum::NATIONAL_COMMUNICATION, $value, true)) {
                    $where->add("$alias.nationalCommunicationRole = true");
                }

                // Phoning national Role
                if (\in_array(AdherentRoleEnum::PHONING_NATIONAL_MANAGER, $value, true)) {
                    $where->add("$alias.phoningManagerRole = :phoningManagerRole");
                    $qb->setParameter('phoningManagerRole', true);
                }

                // PAP national Role
                if (\in_array(AdherentRoleEnum::PAP_NATIONAL_MANAGER, $value, true)) {
                    $where->add("$alias.papNationalManagerRole = :papNationalManagerRole");
                    $qb->setParameter('papNationalManagerRole', true);
                }

                // PAP user Role
                if (\in_array(AdherentRoleEnum::PAP_USER, $value, true)) {
                    $where->add("$alias.papUserRole = :papUserRole");
                    $qb->setParameter('papUserRole', true);
                }

                if ($delegatedTypes = array_intersect(
                    [
                        AdherentRoleEnum::DELEGATED_REFERENT,
                        AdherentRoleEnum::DELEGATED_DEPUTY,
                        AdherentRoleEnum::DELEGATED_SENATOR,
                    ],
                    $value
                )) {
                    $qb->leftJoin(sprintf('%s.receivedDelegatedAccesses', $alias), 'rda');
                    $where->add('rda.type IN (:delegated_types)');
                    $qb->setParameter('delegated_types', array_map(static function ($type) {
                        return substr($type, 10); // remove "delegated_" prefix
                    }, $delegatedTypes));
                }

                // Legislative candidate | Correspondent | Deputy | PAD
                if ($zoneBasedRoles = array_intersect(ZoneBasedRoleTypeEnum::ALL, $value)) {
                    $qb
                        ->leftJoin(sprintf('%s.zoneBasedRoles', $alias), 'zone_based_role')
                        ->setParameter('zone_based_roles', $zoneBasedRoles)
                    ;
                    $where->add('zone_based_role.type IN (:zone_based_roles)');
                }

                if (\in_array(AdherentRoleEnum::SENATORIAL_CANDIDATE, $value, true)) {
                    $qb->leftJoin(sprintf('%s.senatorialCandidateManagedArea', $alias), 'senatorialCandidateManagedArea');
                    $where->add('senatorialCandidateManagedArea IS NOT NULL');
                }

                if ($candidateRoles = array_intersect(AdherentRoleEnum::getCandidates(), $value)) {
                    $qb
                        ->leftJoin(sprintf('%s.candidateManagedArea', $alias), 'candidateManagedArea')
                        ->leftJoin('candidateManagedArea.zone', 'candidate_zone')
                        ->setParameter('candidate_zone_types', array_map(function (string $role) {
                            switch ($role) {
                                case AdherentRoleEnum::CANDIDATE_REGIONAL_HEADED:
                                    return Zone::REGION;
                                case AdherentRoleEnum::CANDIDATE_REGIONAL_LEADER:
                                    return Zone::DEPARTMENT;
                                case AdherentRoleEnum::CANDIDATE_DEPARTMENTAL:
                                    return Zone::CANTON;
                            }
                        }, $candidateRoles))
                    ;

                    $where->add('candidate_zone.type IN (:candidate_zone_types)');
                }

                if ($delegatedCandidateRoles = array_intersect(AdherentRoleEnum::getDelegatedCandidates(), $value)) {
                    if (array_intersect([
                        AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_HEADED,
                        AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_LEADER,
                        AdherentRoleEnum::DELEGATED_CANDIDATE_DEPARTMENTAL,
                    ], $value)) {
                        if (!\in_array('rda', $qb->getAllAliases(), true)) {
                            $qb->leftJoin(sprintf('%s.receivedDelegatedAccesses', $alias), 'rda');
                        }
                        $where->add('rda.type = :delegated_candidate');
                        $qb->setParameter('delegated_candidate', DelegatedAccessEnum::TYPE_CANDIDATE);
                    }

                    $qb
                        ->leftJoin('rda.delegator', 'delegator')
                        ->leftJoin('delegator.candidateManagedArea', 'delegatorCandidateManagedArea')
                        ->leftJoin('delegatorCandidateManagedArea.zone', 'delegator_candidate_zone')
                        ->setParameter('delegator_candidate_zone_types', array_map(function (string $role) {
                            switch ($role) {
                                case AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_HEADED:
                                    return Zone::REGION;
                                case AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_LEADER:
                                    return Zone::DEPARTMENT;
                                case AdherentRoleEnum::DELEGATED_CANDIDATE_DEPARTMENTAL:
                                    return Zone::CANTON;
                            }
                        }, $delegatedCandidateRoles))
                    ;

                    $where->add('delegator_candidate_zone.type IN (:delegator_candidate_zone_types)');
                }

                // thematic community chief
                if (\in_array(AdherentRoleEnum::THEMATIC_COMMUNITY_CHIEF, $value, true)) {
                    $qb->leftJoin(sprintf('%s.handledThematicCommunities', $alias), 'tc');
                    $where->add('tc IS NOT NULL');
                }

                if ($where->count()) {
                    $qb->andWhere($where);
                }

                return true;
            },
        ];
    }
}
