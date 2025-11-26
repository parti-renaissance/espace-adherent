<?php

namespace App\Admin\Filter;

use App\Adherent\AdherentRoleEnum;
use App\Adherent\AdherentRoles;
use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdherentRoleFilter extends AbstractCallbackDecoratorFilter
{
    public function __construct(
        CallbackFilter $decorated,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct($decorated);
    }

    protected function getInitialFilterOptions(): array
    {
        return [
            'field_type' => ChoiceType::class,
            'show_filter' => true,
            'field_options' => [
                'choices' => AdherentRoles::ALL,
                'choice_label' => function (string $label): string {
                    return (isset(ScopeEnum::SCOPE_INSTANCES[$label]) ? ScopeEnum::SCOPE_INSTANCES[$label].' : ' : '').$this->translator->trans("role.$label", ['gender' => 'male']);
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

                // Committee animator
                if (\in_array(AdherentRoleEnum::ANIMATOR, $value, true)) {
                    $qb->leftJoin(\sprintf('%s.animatorCommittees', $alias), 'ac');
                    $where->add('ac IS NOT NULL');
                }

                // National Role
                if (\in_array(ScopeEnum::NATIONAL, $value, true)) {
                    $where->add("$alias.nationalRole = :nationalRole");
                    $qb->setParameter('nationalRole', true);
                }

                // PAP national Role
                if (\in_array(ScopeEnum::PAP_NATIONAL_MANAGER, $value, true)) {
                    $where->add("$alias.papNationalManagerRole = :papNationalManagerRole");
                    $qb->setParameter('papNationalManagerRole', true);
                }

                // Phoning national Role
                if (\in_array(ScopeEnum::PHONING_NATIONAL_MANAGER, $value, true)) {
                    $where->add("$alias.phoningManagerRole = :phoningManagerRole");
                    $qb->setParameter('phoningManagerRole', true);
                }

                // PAP user Role
                if (\in_array(AdherentRoleEnum::PAP_USER, $value, true)) {
                    $where->add("$alias.papUserRole = :papUserRole");
                    $qb->setParameter('papUserRole', true);
                }

                // Meeting scanner user Role
                if (\in_array(ScopeEnum::MEETING_SCANNER, $value, true)) {
                    $where->add("$alias.meetingScanner = :meeting_scanner");
                    $qb->setParameter('meeting_scanner', true);
                }

                // Agora roles Role
                if (\in_array(AdherentRoleEnum::AGORA_PRESIDENT, $value, true)) {
                    $qb->innerJoin("$alias.presidentOfAgoras", 'agora_president');
                }

                if (\in_array(AdherentRoleEnum::AGORA_GENERAL_SECRETARY, $value, true)) {
                    $qb->innerJoin("$alias.generalSecretaryOfAgoras", 'agora_general_secretary');
                }

                // Delegated accesses
                if ($delegatedTypes = array_intersect(
                    [
                        AdherentRoleEnum::DELEGATED_PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                        AdherentRoleEnum::DELEGATED_DEPUTY,
                        AdherentRoleEnum::DELEGATED_ANIMATOR,
                    ],
                    $value
                )) {
                    $qb->leftJoin(\sprintf('%s.receivedDelegatedAccesses', $alias), 'rda');
                    $where->add('rda.type IN (:delegated_types)');
                    $qb->setParameter('delegated_types', array_map(static function ($type) {
                        return substr($type, 10); // remove "delegated_" prefix
                    }, $delegatedTypes));
                }

                // ZoneBasedRole
                if ($zoneBasedRoles = array_intersect(ZoneBasedRoleTypeEnum::ALL, $value)) {
                    $qb
                        ->leftJoin(\sprintf('%s.zoneBasedRoles', $alias), 'zone_based_role')
                        ->setParameter('zone_based_roles', $zoneBasedRoles)
                    ;
                    $where->add('zone_based_role.type IN (:zone_based_roles)');
                }

                if ($where->count()) {
                    $qb->andWhere($where);
                }

                return true;
            },
        ];
    }
}
