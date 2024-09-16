<?php

namespace App\Admin\Email;

use App\Admin\AbstractAdmin;
use App\Mailer\Message\Message;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TransactionalEmailTemplateAdmin extends AbstractAdmin
{
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
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => '#'])
            ->addIdentifier('identifier', null, ['label' => 'Identifiant'])
            ->add('subject', null, ['label' => 'Objet'])
            ->add('parent', null, ['label' => 'Parent'])
            ->add('createdAt', null, ['label' => 'Créé le'])
            ->add('updatedAt', null, ['label' => 'Modifié le'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'content' => ['template' => 'admin/email/list_content.html.twig'],
                    'preview' => ['template' => 'admin/email/list_preview.html.twig'],
                    'send' => ['template' => 'admin/email/list_send_test.html.twig'],
                    'duplicate' => ['template' => 'admin/email/list_duplicate.html.twig'],
                    'sendToProd' => ['template' => 'admin/email/list_send_to_prod.html.twig'],
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
                'choices' => array_combine($classes = $this->getMessageClassNames(), $classes),
                'placeholder' => 'Choisir un identifiant',
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
    }

    private function getMessageClassNames(): array
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

        return $classNames;
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

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $actions = parent::configureActionButtons($buttonList, $action, $object);

        if ('edit' === $action) {
            $actions['content'] = ['template' => 'admin/email/edit_content_button.html.twig'];
        }

        return $actions;
    }
}
