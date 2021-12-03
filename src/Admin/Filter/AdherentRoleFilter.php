<?php

namespace App\Admin\Filter;

use App\Adherent\AdherentRoleEnum;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Coalition\Cause;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccessEnum;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdherentRoleFilter extends CallbackFilter
{
    public function getDefaultOptions()
    {
        return [
            'label' => 'common.role',
            'field_type' => ChoiceType::class,
            'field_options' => [],
            'operator_options' => [],
            'show_filter' => true,
            'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                /** @var QueryBuilder $qb */
                if (!$value['value']) {
                    return false;
                }

                $where = new Expr\Orx();

                // Referent
                if (\in_array(AdherentRoleEnum::REFERENT, $value['value'], true)) {
                    $where->add(sprintf('%s.managedArea IS NOT NULL', $alias));
                }

                // Co-Referent
                if (\in_array(AdherentRoleEnum::COREFERENT, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.referentTeamMember', $alias), 'rtm');
                    $where->add('rtm.id IS NOT NULL');
                }

                // Committee supervisor
                if ($committeeMandates = array_intersect([AdherentRoleEnum::COMMITTEE_SUPERVISOR, AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR], $value['value'])) {
                    $qb->leftJoin(sprintf('%s.adherentMandates', $alias), 'am');
                    $condition = '';
                    if (1 === \count($committeeMandates)) {
                        $condition = ' AND am.provisional = :provisional';
                        $qb->setParameter('provisional', \in_array(AdherentRoleEnum::COMMITTEE_PROVISIONAL_SUPERVISOR, $value['value'], true));
                    }
                    $where->add('am.quality = :supervisor AND am.committee IS NOT NULL AND am.finishAt IS NULL'.$condition);

                    $qb->setParameter('supervisor', CommitteeMandateQualityEnum::SUPERVISOR);
                }

                // Committee host
                if (\in_array(AdherentRoleEnum::COMMITTEE_HOST, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.memberships', $alias), 'ms');
                    $where->add('ms.privilege = :committee_privilege');
                    $qb->setParameter('committee_privilege', CommitteeMembership::COMMITTEE_HOST);
                }

                // Deputy
                if (\in_array(AdherentRoleEnum::DEPUTY, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.managedDistrict', $alias), 'district');
                    $where->add('district IS NOT NULL');
                }

                // Senator
                if (\in_array(AdherentRoleEnum::SENATOR, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.senatorArea', $alias), 'senatorArea');
                    $where->add('senatorArea IS NOT NULL');
                }

                // Board Member
                if (\in_array(AdherentRoleEnum::BOARD_MEMBER, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.boardMember', $alias), 'boardMember');
                    $where->add('boardMember IS NOT NULL');
                }

                // Coordinator
                if (\in_array(AdherentRoleEnum::COORDINATOR, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.coordinatorCommitteeArea', $alias), 'coordinatorCommitteeArea');
                    $where->add('coordinatorCommitteeArea IS NOT NULL');
                }

                // Procuration Manager
                if (\in_array(AdherentRoleEnum::PROCURATION_MANAGER, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.procurationManagedArea', $alias), 'procurationManagedArea');
                    $where->add('procurationManagedArea IS NOT NULL AND procurationManagedArea.codes IS NOT NULL');
                }

                // Cause author
                if (\in_array(AdherentRoleEnum::CAUSE_AUTHOR, $value['value'], true)) {
                    $qb
                        ->leftJoin(sprintf('%s.causes', $alias), 'cause', Expr\Join::WITH, 'cause.status = :cause_approved')
                        ->setParameter('cause_approved', Cause::STATUS_APPROVED)
                    ;
                    $where->add('cause IS NOT NULL');
                }

                // Assessor Manager
                if (\in_array(AdherentRoleEnum::ASSESSOR_MANAGER, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.assessorManagedArea', $alias), 'assessorManagedArea');
                    $where->add('assessorManagedArea IS NOT NULL AND assessorManagedArea.codes IS NOT NULL');
                }

                // Assessor
                if (\in_array(AdherentRoleEnum::ASSESSOR, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.assessorRole', $alias), 'assessorRole');
                    $where->add('assessorRole IS NOT NULL AND assessorRole.votePlace IS NOT NULL');
                }

                // Municipal Manager
                if (\in_array(AdherentRoleEnum::MUNICIPAL_MANAGER, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.municipalManagerRole', $alias), 'municipalManagerRole');
                    $where->add('municipalManagerRole IS NOT NULL');
                }

                // Municipal Manager Supervisor
                if (\in_array(AdherentRoleEnum::MUNICIPAL_MANAGER_SUPERVISOR, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.municipalManagerSupervisorRole', $alias), 'municipalManagerSupervisorRole');
                    $where->add('municipalManagerSupervisorRole IS NOT NULL');
                }

                // Election results reporter
                if (\in_array(AdherentRoleEnum::ELECTION_RESULTS_REPORTER, $value['value'], true)) {
                    $where->add(sprintf('%s.electionResultsReporter = :election_result_reporter', $alias));
                    $qb->setParameter('election_result_reporter', true);
                }

                // J'écoute Manager
                if (\in_array(AdherentRoleEnum::JECOUTE_MANAGER, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.jecouteManagedArea', $alias), 'jecouteManagedArea');
                    $where->add('jecouteManagedArea IS NOT NULL AND jecouteManagedArea.zone IS NOT NULL');
                }

                // User
                if (\in_array(AdherentRoleEnum::USER, $value['value'], true)) {
                    $where->add(sprintf('%s.adherent = 0', $alias));
                }

                // Municipal chief
                if (\in_array(AdherentRoleEnum::MUNICIPAL_CHIEF, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.municipalChiefManagedArea', $alias), 'municipalChiefManagedArea');
                    $where->add('municipalChiefManagedArea IS NOT NULL');
                }

                // Print privilege
                if (\in_array(AdherentRoleEnum::PRINT_PRIVILEGE, $value['value'], true)) {
                    $where->add("$alias.printPrivilege = :printPrivilege");
                    $qb->setParameter('printPrivilege', true);
                }

                // National Role
                if (\in_array(AdherentRoleEnum::ROLE_NATIONAL, $value['value'], true)) {
                    $where->add("$alias.nationalRole = :nationalRole");
                    $qb->setParameter('nationalRole', true);
                }

                // National Communication Role
                if (\in_array(AdherentRoleEnum::ROLE_NATIONAL_COMMUNICATION, $value['value'], true)) {
                    $where->add("$alias.nationalCommunicationRole = true");
                }

                // Phoning national Role
                if (\in_array(AdherentRoleEnum::ROLE_PHONING_MANAGER, $value['value'], true)) {
                    $where->add("$alias.phoningManagerRole = :phoningManagerRole");
                    $qb->setParameter('phoningManagerRole', true);
                }

                // PAP national Role
                if (\in_array(AdherentRoleEnum::ROLE_PAP_NATIONAL_MANAGER, $value['value'], true)) {
                    $where->add("$alias.papNationalManagerRole = :papNationalManagerRole");
                    $qb->setParameter('papNationalManagerRole', true);
                }

                if ($delegatedTypes = array_intersect(
                    [
                        AdherentRoleEnum::DELEGATED_REFERENT,
                        AdherentRoleEnum::DELEGATED_DEPUTY,
                        AdherentRoleEnum::DELEGATED_SENATOR,
                    ],
                    $value['value']
                )) {
                    $qb->leftJoin(sprintf('%s.receivedDelegatedAccesses', $alias), 'rda');
                    $where->add('rda.type IN (:delegated_types)');
                    $qb->setParameter('delegated_types', array_map(static function ($type) {
                        return substr($type, 10); // remove "delegated_" prefix
                    }, $delegatedTypes));
                }

                // LRE
                if (\in_array(AdherentRoleEnum::LRE, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.lreArea', $alias), 'lre');
                    $where->add('lre IS NOT NULL');
                }

                // Legislative candidate
                if (\in_array(AdherentRoleEnum::LEGISLATIVE_CANDIDATE, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.legislativeCandidateManagedDistrict', $alias), 'lcmd');
                    $where->add('lcmd IS NOT NULL');
                }

                if (\in_array(AdherentRoleEnum::SENATORIAL_CANDIDATE, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.senatorialCandidateManagedArea', $alias), 'senatorialCandidateManagedArea');
                    $where->add('senatorialCandidateManagedArea IS NOT NULL');
                }

                if ($candidateRoles = array_intersect(AdherentRoleEnum::getCandidates(), $value['value'])) {
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

                if ($delegatedCandidateRoles = array_intersect(AdherentRoleEnum::getDelegatedCandidates(), $value['value'])) {
                    if (array_intersect([
                        AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_HEADED,
                        AdherentRoleEnum::DELEGATED_CANDIDATE_REGIONAL_LEADER,
                        AdherentRoleEnum::DELEGATED_CANDIDATE_DEPARTMENTAL,
                    ], $value['value'])) {
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
                if (\in_array(AdherentRoleEnum::THEMATIC_COMMUNITY_CHIEF, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.handledThematicCommunities', $alias), 'tc');
                    $where->add('tc IS NOT NULL');
                }

                // Coalition moderator
                if (\in_array(AdherentRoleEnum::COALITION_MODERATOR, $value['value'], true)) {
                    $qb->leftJoin(sprintf('%s.coalitionModeratorRole', $alias), 'coalitionModerator');
                    $where->add('coalitionModerator IS NOT NULL');
                }

                if ($where->count()) {
                    $qb->andWhere($where);
                }

                return true;
            },
        ];
    }

    public function getFieldOptions()
    {
        return array_merge(parent::getFieldOptions(), [
            'choices' => AdherentRoleEnum::toArray(),
            'choice_label' => function (string $value) {
                return $value;
            },
            'multiple' => true,
        ]);
    }
}
