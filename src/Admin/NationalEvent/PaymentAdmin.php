<?php

declare(strict_types=1);

namespace App\Admin\NationalEvent;

use App\Admin\AbstractAdmin;
use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\NationalEventRepository;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PaymentAdmin extends AbstractAdmin
{
    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show', 'export']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('inscription', ModelFilter::class, [
                'label' => 'Inscrit',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'property' => ['search'],
                    'minimum_input_length' => 1,
                    'to_string_callback' => function (EventInscription $inscription): string {
                        return \sprintf(
                            '%s %s (%s, %s)',
                            $inscription->firstName,
                            $inscription->lastName,
                            $inscription->event->getName(),
                            $this->getTranslator()->trans($inscription->status)
                        );
                    },
                ],
            ])
            ->add('inscription.event', null, [
                'label' => 'Event',
                'show_filter' => true,
                'field_options' => [
                    'query_builder' => function (NationalEventRepository $er): QueryBuilder {
                        return $er
                            ->createQueryBuilder('e')
                            ->orderBy('e.startDate', 'DESC')
                        ;
                    },
                ],
            ])
            ->add('transport', null, ['label' => 'Forfait', 'show_filter' => true])
            ->add('accommodation', null, ['label' => 'Hébergement', 'show_filter' => true])
            ->add('toRefund', null, ['label' => 'À rembourser', 'show_filter' => true])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => PaymentStatusEnum::all(),
                    'choice_label' => fn (PaymentStatusEnum $status) => $status->trans($this->getTranslator()),
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('uuid', null, ['label' => 'Uuid'])
            ->add('inscription.event', null, ['label' => 'Event'])
            ->add('inscription', null, ['label' => 'Inscrit', 'template' => 'admin/national_event/list_identity.html.twig'])
            ->add('visitDay', null, ['label' => 'Jour'])
            ->add('packagePlan', null, ['label' => 'Forfait'])
            ->add('transport', null, ['label' => 'Transport'])
            ->add('accommodation', null, ['label' => 'Hébergement'])
            ->add('packageDonation', null, ['label' => 'Don'])
            ->add('withDiscount', null, ['label' => 'Avec réduction'])
            ->add('amountInEuro', null, ['label' => 'Montant €'])
            ->add('status', 'enum', ['label' => 'Statut'])
            ->add('toRefund', null, ['label' => 'À rembourser'])
            ->add('createdAt', null, ['label' => 'Créé le'])
            ->add('updatedAt', null, ['label' => 'Modifié le'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['show' => []]])
        ;
    }

    public function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Général')
                ->add('uuid', null, ['label' => 'Uuid'])
                ->add('inscription.event', null, ['label' => 'Event'])
                ->add('inscription', null, ['label' => 'Inscrit', 'template' => 'admin/national_event/show_identity.html.twig'])
                ->add('_inscription', null, ['label' => 'Détails', 'virtual_field' => true, 'template' => 'admin/national_event/show_details.html.twig'])
                ->add('withDiscount', null, ['label' => 'Avec réduction'])
                ->add('amountInEuro', null, ['label' => 'Montant €'])
                ->add('status', 'enum', ['label' => 'Statut'])
                ->add('toRefund', null, ['label' => 'À rembourser'])
                ->add('createdAt', null, ['label' => 'Créé le'])
                ->add('updatedAt', null, ['label' => 'Modifié le'])
                ->add('payload', null, [
                    'label' => 'Payload',
                    'template' => 'admin/CRUD/show/show_json.html.twig',
                ])
            ->end()
            ->with('Statuts')
                ->add('statuses', null, [
                    'label' => false,
                    'template' => 'admin/national_event/show_payment_statuses.html.twig',
                ])
            ->end()
        ;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }
}
