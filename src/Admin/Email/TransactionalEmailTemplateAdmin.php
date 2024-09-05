<?php

namespace App\Admin\Email;

use App\Admin\AbstractAdmin;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class TransactionalEmailTemplateAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->add('content', $this->getRouterIdParameter().'/content')
            ->add('send_test', $this->getRouterIdParameter().'/send-test')
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
                    'send' => ['template' => 'admin/email/list_send_test.html.twig'],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $template = $this->getSubject();

        $form
            ->add('identifier', null, ['label' => 'Identifiant'])
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

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $actions = parent::configureActionButtons($buttonList, $action, $object);

        if ('edit' === $action) {
            $actions['content'] = ['template' => 'admin/email/edit_content_button.html.twig'];
        }

        return $actions;
    }
}
