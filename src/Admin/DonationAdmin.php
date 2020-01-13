<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Donation;
use AppBundle\Entity\DonationTag;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\Flysystem\Filesystem;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType as FormNumberType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DonationAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    private $storage;

    public function __construct(string $code, string $class, string $baseControllerName, Filesystem $storage)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->storage = $storage;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $typeChoices = [
            'donation.type.'.Donation::TYPE_CHECK => Donation::TYPE_CHECK,
            'donation.type.'.Donation::TYPE_TRANSFER => Donation::TYPE_TRANSFER,
        ];

        if (!$this->isCurrentRoute('create')) {
            $typeChoices['donation.type.'.Donation::TYPE_CB] = Donation::TYPE_CB;
        }

        $form
            ->with('Informations générales', ['class' => 'col-md-6'])
                ->add('donator', ModelAutocompleteType::class, [
                    'label' => 'Donateur',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'identifier',
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                ])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'disabled' => !$this->isCurrentRoute('create'),
                    'choices' => $typeChoices,
                ])
                ->add('amountInEuros', FormNumberType::class, [
                    'label' => 'Montant',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut du don',
                    'disabled' => !$this->isCurrentRoute('create'),
                    'choices' => [
                        'donation.status.'.Donation::STATUS_WAITING_CONFIRMATION => Donation::STATUS_WAITING_CONFIRMATION,
                        'donation.status.'.Donation::STATUS_SUBSCRIPTION_IN_PROGRESS => Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        'donation.status.'.Donation::STATUS_ERROR => Donation::STATUS_ERROR,
                        'donation.status.'.Donation::STATUS_CANCELED => Donation::STATUS_CANCELED,
                        'donation.status.'.Donation::STATUS_FINISHED => Donation::STATUS_FINISHED,
                        'donation.status.'.Donation::STATUS_REFUNDED => Donation::STATUS_REFUNDED,
                    ],
                ])
                ->add('checkNumber', null, [
                    'label' => 'Numéro de chèque',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
                ->add('transferNumber', null, [
                    'label' => 'Numéro de virement',
                    'disabled' => !$this->isCurrentRoute('create'),
                ])
            ->end()
            ->with('Fichier', ['class' => 'col-md-6'])
                ->add('file', FileType::class, [
                    'required' => false,
                    'label' => 'Ajoutez un fichier',
                    'help' => 'Le fichier ne doit pas dépasser 5 Mo.',
                ])
                ->add('removeFile', CheckboxType::class, [
                    'label' => 'Supprimer le fichier ?',
                    'required' => false,
                ])
            ->end()
            ->with('Administration', ['class' => 'col-md-6'])
                ->add('tags', null, [
                    'label' => 'Tags',
                ])
                ->add('comment', null, [
                    'label' => 'Commentaire',
                ])
            ->end()

        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('donator', ModelAutocompleteFilter::class, [
                'label' => 'Donateur',
                'show_filter' => true,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'identifier',
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                ],
            ])
            ->add('type', null, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        Donation::TYPE_CB,
                        Donation::TYPE_CHECK,
                        Donation::TYPE_TRANSFER,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ],
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        Donation::STATUS_WAITING_CONFIRMATION,
                        Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        Donation::STATUS_ERROR,
                        Donation::STATUS_CANCELED,
                        Donation::STATUS_FINISHED,
                        Donation::STATUS_REFUNDED,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.status.'.$choice;
                    },
                ],
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags',
                'show_filter' => true,
                'field_options' => [
                    'class' => DonationTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('donator', null, [
                'label' => 'Donateur',
            ])
            ->add('amountInEuros', NumberType::class, [
                'label' => 'Montant',
                'template' => 'admin/donation/list_amount.html.twig',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/donation/list_type.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut du don',
                'template' => 'admin/donation/list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
                'template' => 'admin/donation/list_tags.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    /**
     * @param Donation $donation
     */
    public function prePersist($donation)
    {
        parent::prePersist($donation);

        $this->handleFile($donation);
    }

    /**
     * @param Donation $donation
     */
    public function preUpdate($donation)
    {
        parent::preUpdate($donation);

        $this->handleFile($donation);
    }

    /**
     * @param Donation $donation
     */
    public function postRemove($donation)
    {
        parent::postRemove($donation);

        $filePath = $donation->getFilePathWithDirectory();

        if ($this->storage->has($filePath)) {
            $this->storage->delete($filePath);
        }
    }

    public function handleFile(Donation $donation): void
    {
        $filepath = $donation->getFilePathWithDirectory();

        if ($donation->getRemoveFile() && $this->storage->has($filepath)) {
            $this->storage->delete($filepath);
            $donation->removeFilename();

            return;
        }

        $this->uploadFile($donation);
    }

    public function uploadFile(Donation $donation): void
    {
        $uploadedFile = $donation->getFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $donation->setFilenameFromUploadedFile();

        $this->storage->put($donation->getFilePathWithDirectory(), file_get_contents($donation->getFile()->getPathname()));
    }
}
