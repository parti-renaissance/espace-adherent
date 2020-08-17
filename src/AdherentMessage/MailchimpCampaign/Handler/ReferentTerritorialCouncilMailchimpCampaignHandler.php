<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentTerritorialCouncilFilter;
use App\Entity\AdherentMessage\ReferentTerritorialCouncilMessage;
use App\Repository\TerritorialCouncil\TerritorialCouncilRepository;

class ReferentTerritorialCouncilMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    private $territorialCouncilRepository;

    public function __construct(TerritorialCouncilRepository $territorialCouncilRepository)
    {
        $this->territorialCouncilRepository = $territorialCouncilRepository;
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof ReferentTerritorialCouncilMessage;
    }

    /**
     * @param AdherentMessageFilterInterface|ReferentTerritorialCouncilFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        if (!$referentTag = $filter->getReferentTag()) {
            throw new \InvalidArgumentException('There should be 1 selected tag for this campaign.');
        }

        $council = $this->territorialCouncilRepository->findOneByReferentTag($referentTag);

        return [
            [
                [
                    'type' => 'static_segment',
                    'value' => $council->getMailchimpId(),
                    'label' => $tagLabel = $council->getCodes(),
                ],
            ],
        ];
    }
}
