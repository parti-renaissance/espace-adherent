<?php

namespace App\Controller\Admin;

use App\Entity\SmsCampaign\SmsCampaign;
use App\OvhCloud\Driver;
use App\Repository\AdherentRepository;
use App\SmsCampaign\Command\SendSmsCampaignCommand;
use App\SmsCampaign\Statistics;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminSmsCampaignCRUDController extends CRUDController
{
    private AdherentRepository $adherentRepository;
    private EntityManagerInterface $entityManager;
    private Driver $ovhDriver;

    public function __construct(
        AdherentRepository $adherentRepository,
        EntityManagerInterface $entityManager,
        Driver $ovhDriver
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
        $this->ovhDriver = $ovhDriver;
    }

    public function confirmAction(SmsCampaign $smsCampaign): Response
    {
        if (!$smsCampaign->isDraft()) {
            $this->addFlash('sonata_flash_error', 'Cette campagne ne peut pas être envoyée');

            return $this->redirectToList();
        }

        $paginator = $this->adherentRepository->findForSmsCampaign($smsCampaign, true);

        if ($paginator->getTotalItems() !== $smsCampaign->getRecipientCount()) {
            $smsCampaign->setRecipientCount($paginator->getTotalItems());
            $smsCampaign->setAdherentCount($this->adherentRepository->findForSmsCampaign($smsCampaign, false)->getTotalItems());
            $this->entityManager->flush();
        }

        return $this->renderWithExtraParams('admin/sms_campaign/confirm.html.twig', [
            'action' => 'show',
            'object' => $smsCampaign,
        ]);
    }

    public function sendAction(SmsCampaign $smsCampaign, MessageBusInterface $bus): Response
    {
        if (!$smsCampaign->isDraft()) {
            $this->addFlash('sonata_flash_error', 'Cette campagne ne peut pas être envoyée');

            return $this->redirectToList();
        }

        $paginator = $this->adherentRepository->findForSmsCampaign($smsCampaign, true);

        if ($paginator->getTotalItems() < 1) {
            $this->addFlash('sonata_flash_error', 'Cette campagne ne vise aucun contact.');

            return $this->redirectToList();
        }

        $smsCampaign->send();
        $this->entityManager->flush();

        $bus->dispatch(new SendSmsCampaignCommand($smsCampaign->getId()));

        $this->addFlash('sonata_flash_success', sprintf('La campagne "%s" est en cours d\'envoi', $smsCampaign->getTitle()));

        return $this->redirectToList();
    }

    protected function redirectTo($object)
    {
        $request = $this->getRequest();

        if (null !== $request->get('btn_edit_and_confirm')) {
            return $this->redirect($this->admin->generateUrl('confirm', ['id' => $object->getId()]));
        }

        return parent::redirectTo($object);
    }

    /** @param SmsCampaign $object */
    protected function preShow(Request $request, $object)
    {
        if ($object->getExternalId()) {
            $data = $this->ovhDriver->getBatchStats($object->getExternalId())->toArray(false);

            if ($data) {
                $object->statistics = Statistics::createFromResponse($data);
            }
        }

        return null;
    }
}
