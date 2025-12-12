<?php

declare(strict_types=1);

namespace App\Admin;

use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\Filter\UtmFilter;
use App\Entity\PetitionSignature;
use App\Form\CivilityType;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Utils\PhoneNumberUtils;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PetitionSignatureAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('petitionName', null, ['label' => 'Pétition', 'disabled' => true])
                ->add('civility', CivilityType::class, ['label' => 'Civilité'])
                ->add('firstName', null, ['label' => 'Prénom'])
                ->add('lastName', null, ['label' => 'Nom'])
                ->add('emailAddress', null, ['label' => 'Email'])
                ->add('postalCode', null, ['label' => 'Code postal'])
                ->add('phone', null, ['label' => 'Tél'])
            ->end()
            ->with('Autre', ['class' => 'col-md-6'])
                ->add('createdAt', null, ['label' => 'Créée le', 'widget' => 'single_text', 'disabled' => true])
                ->add('validatedAt', null, ['label' => 'Email confirmé', 'widget' => 'single_text', 'disabled' => true])
                ->add('newsletter', null, ['label' => 'Accepte recevoir la communication', 'disabled' => true])
                ->add('utmSource', null, ['label' => 'UTM Source'])
                ->add('utmCampaign', null, ['label' => 'UTM Campagne'])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('petitionName', null, ['label' => 'Pétition'])
            ->add('lastName', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/CRUD/list_identity.html.twig',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('postalCode', null, ['label' => 'Code postal'])
            ->add('validatedAt', 'boolean', ['label' => 'Email confirmé'])
            ->add('newsletter', 'boolean', ['label' => 'Newsletter'])
            ->add('utm', null, [
                'label' => 'UTM',
                'virtual_field' => true,
                'template' => 'admin/CRUD/list/utm_list.html.twig',
            ])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'link' => ['template' => 'admin/petition/list__action_link.html.twig'],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('petitionName', ChoiceFilter::class, [
                'label' => 'Pétition',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choice_loader' => new CallbackChoiceLoader(function () {
                        $petitions = $this->getModelManager()->createQuery(PetitionSignature::class, 's')
                            ->select('DISTINCT s.petitionName')
                            ->where('s.petitionName IS NOT NULL')
                            ->orderBy('s.petitionName')
                            ->getQuery()
                            ->getSingleColumnResult()
                        ;

                        return array_combine($petitions, $petitions);
                    }),
                ],
            ])
            ->add('search', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ['adherent.firstName', 'adherent.lastName'],
                            ['adherent.lastName', 'adherent.firstName'],
                            ['adherent.emailAddress', 'adherent.emailAddress'],
                            ["$alias.firstName", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstName"],
                            ["$alias.emailAddress", "$alias.emailAddress"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                        ]
                    );

                    return true;
                },
            ])
            ->add('postalCode', null, ['label' => 'Code postal', 'show_filter' => true])
            ->add('adherentType', CallbackFilter::class, [
                'label' => 'Type d\'adhérent',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Adhérent' => 'adherent',
                        'Sympathisant' => 'sympathisant',
                        'Citoyen' => false,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $value = $value->getValue();

                    if (false === $value) {
                        $qb->andWhere($alias.'.adherent IS NULL');

                        return true;
                    }

                    $qb
                        ->innerJoin($alias.'.adherent', 'a')
                        ->andWhere('a.tags LIKE :adherent_type')
                        ->setParameter('adherent_type', $value.':%')
                    ;

                    return true;
                },
            ])
            ->add('validatedAt', CallbackFilter::class, [
                'label' => 'Email confirmé',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    if ($value->getValue()) {
                        $qb->andWhere("$alias.validatedAt IS NOT NULL");
                    } else {
                        $qb->andWhere("$alias.validatedAt IS NULL");
                    }

                    return true;
                },
            ])
            ->add('utm', UtmFilter::class, ['label' => 'UTM Source / Campagne', 'show_filter' => true])
        ;
    }

    protected function configureExportFields(): array
    {
        return [IteratorCallbackDataSource::CALLBACK => static function (array $signature) {
            /** @var PetitionSignature $signature */
            $signature = $signature[0];

            $adherentType = 'Citoyen';
            if ($signature->adherent) {
                $adherentType = $signature->adherent->isRenaissanceAdherent() ? 'Adhérent' : 'Sympathisant';
            }

            return [
                'Pétition' => $signature->petitionName,
                'Adherent' => $adherentType,
                'Civilité' => $signature->civility?->value(),
                'Prénom' => $signature->firstName,
                'Nom' => $signature->lastName,
                'Email' => $signature->emailAddress,
                'Code postal' => $signature->postalCode,
                'Tél' => PhoneNumberUtils::format($signature->phone),
                'Créée le' => $signature->getCreatedAt()?->format('d/m/Y H:i:s'),
                'Validée le' => $signature->validatedAt?->format('d/m/Y H:i:s'),
                'Uuid' => $signature->getUuid()->toString(),
                'UTM Source' => $signature->utmSource,
                'UTM Campagne' => $signature->utmCampaign,
            ];
        }];
    }
}
