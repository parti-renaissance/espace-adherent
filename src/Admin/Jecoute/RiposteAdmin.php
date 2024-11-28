<?php

namespace App\Admin\Jecoute;

use App\Entity\Administrator;
use App\Entity\Jecoute\Riposte;
use App\Jecoute\RiposteHandler;
use App\Riposte\RiposteOpenGraphHandler;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Service\Attribute\Required;

class RiposteAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    private $security;
    /** @var RiposteOpenGraphHandler */
    private $openGraphHandler;

    /** @var RiposteHandler */
    private $riposteHandler;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('body', null, [
                'label' => 'Texte',
            ])
            ->add('sourceUrl', null, [
                'label' => 'Url',
            ])
            ->add('withNotification', null, [
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('body', null, [
                'label' => 'Texte',
            ])
            ->add('sourceUrl', null, [
                'label' => 'URL',
            ])
            ->add('withNotification', null, [
                'editable' => true,
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'editable' => true,
                'label' => 'Active',
            ])
            ->add('creator', null, [
                'virtual_field' => true,
                'label' => 'Auteur',
                'template' => 'admin/jecoute/riposte/list_creator.html.twig',
            ])
            ->add('nbViews', null, [
                'label' => 'Nb de vues',
                'header_style' => 'max-width: 70px',
            ])
            ->add('nbDetailViews', null, [
                'label' => 'Nb de vues détaillées',
                'header_style' => 'max-width: 70px',
            ])
            ->add('nbSourceViews', null, [
                'label' => 'Nb de vues de la source',
                'header_style' => 'max-width: 70px',
            ])
            ->add('nbRipostes', null, [
                'label' => 'Nb de ripostes',
                'header_style' => 'max-width: 70px',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('title', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Texte',
                'attr' => ['rows' => 10],
            ])
            ->add('sourceUrl', UrlType::class, [
                'label' => 'URL',
            ])
            ->add('withNotification', null, [
                'label' => 'Avec notification',
                'disabled' => !$this->isCurrentRoute('create'),
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;

        $form->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function submit(FormEvent $event): void
    {
        /** @var Riposte $riposte */
        $riposte = $event->getData();

        $this->openGraphHandler->handle($riposte);
    }

    /**
     * @param Riposte $object
     */
    protected function prePersist(object $object): void
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setCreatedBy($administrator);
    }

    /**
     * @param Riposte $object
     */
    protected function postPersist(object $object): void
    {
        parent::postPersist($object);

        $this->dispatchNotification($object);
    }

    private function dispatchNotification(Riposte $riposte): void
    {
        $this->riposteHandler->handleNotification($riposte);
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    #[Required]
    public function setOpenGraphHandler(RiposteOpenGraphHandler $openGraphHandler): void
    {
        $this->openGraphHandler = $openGraphHandler;
    }

    #[Required]
    public function setRiposteHandler(RiposteHandler $riposteHandler): void
    {
        $this->riposteHandler = $riposteHandler;
    }
}
