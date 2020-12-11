<?php

namespace App\Admin\Jecoute;

use App\Entity\Jecoute\News;
use App\JeMarche\JeMarcheDeviceNotifier;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class NewsAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
    ];

    private $deviceNotifier;

    public function __construct($code, $class, $baseControllerName, JeMarcheDeviceNotifier $deviceNotifier)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->deviceNotifier = $deviceNotifier;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('text', TextareaType::class, [
                    'label' => 'Texte',
                ])
                ->add('externalLink', UrlType::class, [
                    'label' => 'Lien',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('title', null, [
                'label' => 'Nom',
            ])
            ->add('createdAt', null, [
                'label' => 'Création',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param News $object
     */
    public function postPersist($object)
    {
        parent::postPersist($object);

        $this->dispatchNotification($object);
    }

    private function dispatchNotification(News $news): void
    {
        $this->deviceNotifier->sendNotification($news);
    }
}
