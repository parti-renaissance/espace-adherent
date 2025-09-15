<?php

namespace App\Admin\Email;

use App\Admin\AbstractAdmin;
use App\Entity\Email\TransactionalEmailTemplate;
use App\Mailer\Message\Message;
use App\Repository\Email\TransactionalEmailTemplateRepository;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransactionalEmailTemplateAdmin extends AbstractAdmin
{
    private ?TransactionalEmailTemplate $beforeUpdate = null;

    public function __construct(
        private readonly string $appEnvironment,
        private readonly TransactionalEmailTemplateRepository $transactionEmailTemplateRepository,
    ) {
        parent::__construct();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->add('content', $this->getRouterIdParameter().'/content')
            ->add('send_test', $this->getRouterIdParameter().'/send-test')
            ->add('preview', $this->getRouterIdParameter().'/visualiser')
            ->add('preview_content', $this->getRouterIdParameter().'/preview-content')
            ->add('duplicate', $this->getRouterIdParameter().'/duplicate')
            ->add('sendToProd', $this->getRouterIdParameter().'/send-to-prod')
        ;
    }

    protected function getAccessMapping(): array
    {
        return [
            'content' => 'EDIT',
            'send_test' => 'EDIT',
            'preview' => 'EDIT',
            'preview_content' => 'EDIT',
            'duplicate' => 'EDIT',
        ];
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('identifier', null, ['label' => 'Identifiant', 'show_filter' => true])
            ->add('subject', null, ['label' => 'Objet', 'show_filter' => true])
            ->add('parent', null, ['label' => 'Parent'])
        ;
        if ('production' !== $this->appEnvironment) {
            $filter->add('isSync', null, ['label' => 'Synchro avec la PROD', 'show_filter' => true]);
        }
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => '#'])
            ->add('messageClass', 'url', ['label' => 'Identifiant', 'route' => ['name' => 'admin_app_email_transactionalemailtemplate_content', 'identifier_parameter_name' => 'id']])
            ->add('subject', null, ['label' => 'Objet'])
        ;

        if ('production' !== $this->appEnvironment) {
            $list->add('isSync', null, ['label' => 'Sync avec Prod']);
        }

        $list
            ->add('parent', null, ['label' => 'Parent', 'route' => ['name' => 'content']])
            ->add('updatedAt', null, ['label' => 'Modifié le'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => ['template' => 'admin/email/list_edit.html.twig'],
                    'content' => ['template' => 'admin/email/list_content.html.twig'],
                    'preview' => ['template' => 'admin/email/list_preview.html.twig'],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $template = $this->getSubject();

        $form
            ->add('identifier', ChoiceType::class, [
                'label' => 'Identifiant',
                'required' => false,
                'choices' => array_combine($classes = $this->getMessageClassNames($template), $classes),
                'placeholder' => 'Choisir un identifiant',
                'attr' => ['data-sonata-select2-allow-tags' => 'true'],
            ])
            ->add('subject', null, ['label' => 'Objet', 'required' => false])
            ->add('parent', null, [
                'label' => 'Template parent',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($template) {
                    $qb = $er->createQueryBuilder('template')->where('template.parent IS NULL');
                    if ($template->getId()) {
                        $qb->andWhere('template != :template')->setParameter('template', $template);
                    }

                    return $qb;
                },
            ])
        ;
        $form->getFormBuilder()->get('identifier')->resetViewTransformers();
    }

    private function getMessageClassNames(?TransactionalEmailTemplate $currentTemplate): array
    {
        $classNames = [];
        foreach ($this->scanDir($dir = \dirname((new \ReflectionClass(Message::class))->getFileName())) as $file) {
            if ('php' === pathinfo($file, \PATHINFO_EXTENSION)) {
                require_once $file;
            }
        }

        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, Message::class)) {
                $reflectionClass = new \ReflectionClass($class);
                if (!$reflectionClass->isAbstract()) {
                    $classNames[] = $class;
                }
            }
        }

        $existingTemplates = array_filter(
            $this->transactionEmailTemplateRepository->findAll(),
            function (TransactionalEmailTemplate $template) use ($currentTemplate) { return $template->getId() != $currentTemplate?->getId(); }
        );

        $templates = array_map(fn (TransactionalEmailTemplate $template) => $template->identifier, $existingTemplates);

        return array_diff(array_merge($classNames, array_filter([$currentTemplate?->identifier])), $templates);
    }

    private function scanDir(string $directory): array
    {
        $files = [];

        foreach (scandir($directory) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }

            $filePath = $directory.\DIRECTORY_SEPARATOR.$file;

            if (is_dir($filePath)) {
                $files = array_merge($files, $this->scanDir($filePath));
            } else {
                $files[] = $filePath;
            }
        }

        return $files;
    }

    protected function alterObject(object $object): void
    {
        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $object;
        }
    }

    /**
     * @param TransactionalEmailTemplate $object
     */
    protected function preUpdate(object $object): void
    {
        if (!$this->beforeUpdate) {
            return;
        }

        $object->isSync =
            $this->beforeUpdate->getJsonContent() === $object->getJsonContent()
            && $this->beforeUpdate->subject === $object->subject
            && $this->beforeUpdate->identifier === $object->identifier
            && $this->beforeUpdate->parent === $object->parent;
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $actions = parent::configureActionButtons($buttonList, $action, $object);

        if ('edit' === $action) {
            $actions['content'] = ['template' => 'admin/email/edit_content_button.html.twig'];
        }

        return $actions;
    }
}
