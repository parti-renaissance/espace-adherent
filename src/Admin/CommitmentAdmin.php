<?php

namespace App\Admin;

use App\Entity\Commitment;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvents;

class CommitmentAdmin extends AbstractAdmin
{
    protected function configureDefaultFilterValues(array &$filterValues)
    {
        $filterValues = array_merge($filterValues, [
            '_sort_order' => 'ASC',
            '_sort_by' => 'position',
        ]);
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('title', null, ['label' => 'Titre'])
            ->add('position', null, ['label' => 'Position'])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add('updatedAt', null, ['label' => 'Modifiée le'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'move' => [
                        'template' => '@PixSortableBehavior/Default/_sort_drag_drop.html.twig',
                        'enable_top_bottom_buttons' => true,
                    ],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('position', IntegerType::class, [
                    'attr' => ['min' => 0],
                    'label' => 'Position',
                    'help' => 'Plus la position est élevée plus le block descendra sur la page.',
                ])
                ->add('shortDescription', null, ['label' => 'Description courte'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description complète',
                    'attr' => ['class' => 'simplified-content-editor', 'rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-6'])
                ->add('image', FileType::class, [
                    'label' => 'Ajoutez une photo',
                    'help' => 'La photo ne doit pas dépasser 5 Mo.',
                    'required' => $this->isCreation(),
                ])
            ->end()
        ;

        // Increase the position according to the total number of commitments in DB
        $form->getFormBuilder()->addEventListener(FormEvents::PRE_SET_DATA, function (PreSetDataEvent $event) {
            /** @var Commitment $commitment */
            if ((!$commitment = $event->getData()) || $commitment->getId()) {
                return;
            }

            if ($commitment->position === 0) {
                $queryBuilder = $this->getModelManager()->createQuery(Commitment::class)->select('COUNT(1)');
                $commitment->position = (1 + current(current($this->getModelManager()->executeQuery($queryBuilder))));
            }
        });
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
        $collection->add('move', $this->getRouterIdParameter().'/move/{position}');
    }
}
