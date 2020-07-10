<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\ReferentElectedRepresentativeMessage;
use App\Entity\MailchimpSegment;
use App\Repository\MailchimpSegmentRepository;

class ReferentElectedRepresentativeMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    private $mailchimpSegmentRepository;

    public function __construct(MailchimpSegmentRepository $mailchimpSegmentRepository)
    {
        $this->mailchimpSegmentRepository = $mailchimpSegmentRepository;
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof ReferentElectedRepresentativeMessage;
    }

    /**
     * @param AdherentMessageFilterInterface|ReferentElectedRepresentativeFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $filters = [];

        foreach ($filter->getReferentTags() as $tag) {
            $label = $tag->getCode();

            if (!$mailchimpSegment = $this->findMailchimpSegment($label)) {
                $mailchimpSegment = MailchimpSegment::createElectedRepresentativeSegment($label);
            }

            $staticSegmentCondition = [
                'type' => 'mailchimp_segment',
                'value' => $mailchimpSegment,
                'label' => $tag->getCode(),
            ];

            $filters[] = [$staticSegmentCondition];
        }

        return $filters;
    }

    private function findMailchimpSegment(string $label): ?MailchimpSegment
    {
        return $this->mailchimpSegmentRepository->findOneForElectedRepresentative($label);
    }
}
