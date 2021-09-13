<?php

namespace App\Admin;

use App\Admin\Audience\AudienceAdmin;
use App\Entity\SmsCampaign;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\Form\Audience\AudienceSnapshotType;
use App\Repository\AdherentRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Security;

class SmsCampaignAdmin extends AbstractAdmin
{
    /** @var Security */
    private $security;
    /** @var AdherentRepository */
    private $adherentRepository;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('confirm', $this->getRouterIdParameter().'/confirmation')
            ->add('send', $this->getRouterIdParameter().'/envoyer')
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('title', null, ['label' => 'Titre'])
            ->add('content', null, ['label' => 'Contenu'])
            ->add('status', 'trans', ['label' => 'Statut', 'format' => 'sms_campaign.status.%s'])
            ->add('recipientCount', null, ['label' => 'Nb contacts'])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add('sentAt', null, ['label' => 'Envoyée le'])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => ['template' => 'admin/sms_campaign/CRUD/list__action_edit.html.twig'],
                    'confirm' => ['template' => 'admin/sms_campaign/CRUD/list__action_confirm.html.twig'],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Contenu')
                ->add('title', null, ['label' => 'Titre'])
                ->add('content', TextareaType::class, ['label' => 'Contenu', 'help' => '149 caractères maximum'])
            ->end()
            ->with('Filtres')
                ->add('audience', AudienceSnapshotType::class, ['label' => false])
            ->end()
        ;

        $form->get('audience')->add('zones', AdminZoneAutocompleteType::class, [
            'required' => false,
            'multiple' => true,
            'model_manager' => $this->getModelManager(),
            'admin_code' => AudienceAdmin::SERVICE_CODE,
        ]);
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('title', null, ['label' => 'Titre'])
            ->add('content', null, ['label' => 'Contenu'])
            ->add('audience', null, ['label' => 'Audience'])
            ->add('recipientCount', null, ['label' => 'Nombre de contact trouvé'])
            ->add('status', 'trans', ['label' => 'Statut', 'format' => 'sms_campaign.status.%s'])
            ->add('responsePayload', null, ['label' => 'Réponse'])
            ->add('externalId', null, ['label' => 'OVH ID'])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add('sendAt', null, ['label' => 'Envoyée le'])
        ;
    }

    /** @param SmsCampaign $object */
    public function prePersist($object)
    {
        $object->setAdministrator($this->security->getUser());

        $this->updateRecipientCount($object);
    }

    /** @param SmsCampaign $object */
    public function preUpdate($object)
    {
        $this->updateRecipientCount($object);
    }

    private function updateRecipientCount(SmsCampaign $object): void
    {
        $paginator = $this->adherentRepository->findForSmsCampaign($object);
        $object->setRecipientCount($paginator->getTotalItems());
    }

    /** @required */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    /** @required */
    public function setAdherentRepository(AdherentRepository $adherentRepository): void
    {
        $this->adherentRepository = $adherentRepository;
    }
}
