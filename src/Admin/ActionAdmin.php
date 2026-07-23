<?php

declare(strict_types=1);

namespace App\Admin;

use App\Action\ActionTypeEnum;
use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Form\Admin\AdherentAutocompleteType;
use App\Form\ReCountryType;
use App\Geocoder\Exception\GeocodingException;
use App\Geocoder\Geocoder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ActionAdmin extends AbstractAdmin implements ZoneableAdminInterface
{
    private const array STATUS_CHOICES = [
        Action::STATUS_SCHEDULED => 'Programmé',
        Action::STATUS_CANCELLED => 'Annulé',
    ];

    public function __construct(private readonly Geocoder $geocoder)
    {
        parent::__construct();
    }

    public function prePersist(object $object): void
    {
        $this->refreshCoordinates($object);
    }

    public function preUpdate(object $object): void
    {
        $this->refreshCoordinates($object);
    }

    public function toString(object $object): string
    {
        if (!$object instanceof Action) {
            return parent::toString($object);
        }

        $label = ActionTypeEnum::LABELS[$object->type] ?? (string) $object->type;

        return null !== $object->date
            ? \sprintf('%s du %s', ucfirst($label), $object->date->format('d/m/Y'))
            : ucfirst($label);
    }

    /**
     * @return array<string, string>
     */
    private function typeChoices(): array
    {
        return array_flip(ActionTypeEnum::LABELS);
    }

    private function refreshCoordinates(object $object): void
    {
        if (!$object instanceof Action) {
            return;
        }

        $hash = md5($address = $object->getGeocodableAddress());

        if ($object->getGeocodableHash() === $hash) {
            return;
        }

        try {
            $object->updateCoordinates($this->geocoder->geocode($address));
            $object->setGeocodableHash($hash);
        } catch (GeocodingException) {
            $object->resetCoordinates();
        }
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAliases()[0];

        $query
            ->addSelect('_organizer')
            ->leftJoin($alias.'.author', '_organizer')
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
            ->remove('export')
            ->add('cancel', $this->getRouterIdParameter().'/cancel')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/action/list_type.html.twig',
            ])
            ->add('description', 'html', [
                'label' => 'Description',
                'strip' => true,
                'truncate' => ['length' => 120, 'separator' => '…'],
            ])
            ->add('author', null, ['label' => 'Organisateur'])
            ->add('inlineFormattedAddress', 'string', ['label' => 'Lieu'])
            ->add('participantsCount', 'integer', [
                'label' => 'Participants',
            ])
            ->add('status', 'choice', [
                'label' => 'Statut',
                'choices' => self::STATUS_CHOICES,
            ])
            ->add('date', null, ['label' => 'Date de début'])
            ->add('createdAt', null, ['label' => 'Créé le'])
            ->add('updatedAt', null, ['label' => 'Modifié le'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'cancel' => ['template' => 'admin/action/list_action_cancel.html.twig'],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('author', ModelFilter::class, [
                'label' => 'Organisateur',
                'show_filter' => true,
                'field_type' => AdherentAutocompleteType::class,
                'field_options' => [
                    'class' => Adherent::class,
                    'model_manager' => $this->getModelManager(),
                    'req_params' => [
                        AdherentAdmin::ADHERENT_AUTOCOMPLETE_FILTER_METHOD_PARAM_NAME => 'autocompleteCallbackFilterActionAuthors',
                    ],
                ],
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => $this->typeChoices(),
                ],
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(self::STATUS_CHOICES),
                ],
            ])
            ->add('location', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'field_options' => [
                    'attr' => ['placeholder' => 'Ville, code postal, adresse'],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value): bool {
                    $term = $value->hasValue() ? trim((string) $value->getValue()) : '';

                    if ('' === $term) {
                        return false;
                    }

                    $qb->getQueryBuilder()
                        ->andWhere(\sprintf('(%1$s.postAddress.cityName LIKE :location OR %1$s.postAddress.postalCode LIKE :location OR %1$s.postAddress.address LIKE :location)', $alias))
                        ->setParameter('location', '%'.$term.'%')
                    ;

                    return true;
                },
            ])
            ->add('date', DateRangeFilter::class, [
                'label' => 'Date',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('updatedAt', DateRangeFilter::class, [
                'label' => 'Date de dernière modification',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Action', ['class' => 'col-md-7'])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => $this->typeChoices(),
                ])
                ->add('date', null, [
                    'label' => 'Date',
                    'widget' => 'single_text',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-5'])
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                    'required' => false,
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                    'required' => false,
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                    'required' => false,
                ])
                ->add('postAddress.country', ReCountryType::class, [
                    'label' => 'Pays',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Action', ['class' => 'col-md-7'])
                ->add('type', null, ['label' => 'Type'])
                ->add('description', null, ['label' => 'Description'])
                ->add('date', null, ['label' => 'Date'])
                ->add('author', null, ['label' => 'Organisateur'])
                ->add('status', 'choice', [
                    'label' => 'Statut',
                    'choices' => self::STATUS_CHOICES,
                ])
                ->add('canceledAt', null, ['label' => 'Annulée le'])
                ->add('postAddress.address', null, ['label' => 'Rue'])
                ->add('postAddress.postalCode', null, ['label' => 'Code postal'])
                ->add('postAddress.cityName', null, ['label' => 'Ville'])
                ->add('postAddress.country', null, ['label' => 'Pays'])
            ->end()
            ->with('Participants', ['class' => 'col-md-5'])
                ->add('participants', null, [
                    'label' => false,
                    'template' => 'admin/action/show_participants.html.twig',
                ])
            ->end()
        ;
    }
}
