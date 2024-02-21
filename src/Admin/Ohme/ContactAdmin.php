<?php

namespace App\Admin\Ohme;

use App\Entity\Adherent;
use App\Entity\Ohme\Contact;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ContactAdmin extends AbstractAdmin
{
    private ?Adherent $adherentBeforeUpdate = null;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('adherent', ModelAutocompleteType::class, [
                    'label' => 'Adhérent',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getId()
                        );
                    },
                    'btn_add' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('search', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $search = $value->getValue();

                    $conditions = $qb->expr()->orX();

                    preg_match('/(?<first>.*) (?<last>.*)/', $search, $tokens);

                    if (\array_key_exists('first', $tokens) && \array_key_exists('last', $tokens)) {
                        $conditions
                            ->add("($alias.firstName LIKE :search_first_token AND $alias.lastName LIKE :search_last_token)")
                            ->add("($alias.firstName LIKE :search_last_token AND $alias.lastName LIKE :search_first_token)")
                            ->add("($alias.email LIKE :search_first_token AND $alias.email LIKE :search_last_token)")
                        ;

                        $qb
                            ->setParameter('search_first_token', '%'.$tokens['first'].'%')
                            ->setParameter('search_last_token', '%'.$tokens['last'].'%')
                        ;
                    } else {
                        $conditions
                            ->add("$alias.firstName LIKE :search")
                            ->add("$alias.lastName LIKE :search")
                            ->add("$alias.email LIKE :search")
                        ;
                    }

                    $conditions
                        ->add("REPLACE(REPLACE($alias.phone, ' ', ''), '+', '') LIKE REPLACE(REPLACE(:search, ' ', ''), '+', '')")
                        ->add("$alias.identifier = REPLACE(:strict_search, ' ', '')")
                    ;

                    $qb
                        ->andWhere($conditions)
                        ->setParameter('search', "%$search%")
                        ->setParameter('strict_search', $search)
                    ;

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('fullname', null, [
                'label' => 'Nom',
                'virtual_field' => true,
                'template' => 'admin/ohme/contact/list_fullname.html.twig',
            ])
            ->add('address', null, [
                'label' => 'Adresse',
                'virtual_field' => true,
                'template' => 'admin/ohme/contact/list_address.html.twig',
            ])
            ->add('ohmeDates', null, [
                'label' => 'Dates (Ohme)',
                'virtual_field' => true,
                'template' => 'admin/ohme/contact/list_ohme_dates.html.twig',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'ohmeCreatedAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param Contact $object
     */
    protected function alterObject(object $object): void
    {
        $this->adherentBeforeUpdate = $object->adherent;
    }

    /**
     * @param Contact $object
     */
    protected function preUpdate(object $object): void
    {
        if ($this->adherentBeforeUpdate !== $object->adherent) {
            // $this->contactHandler->updateAdherentLink($object);
        }
    }
}
