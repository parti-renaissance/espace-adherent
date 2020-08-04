<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentElectedRepresentativeFilter;
use App\Entity\AdherentMessage\ReferentElectedRepresentativeMessage;
use App\Entity\MailchimpSegment;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use App\Repository\MailchimpSegmentRepository;
use Symfony\Component\Translation\TranslatorInterface;

class ReferentElectedRepresentativeMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    private $mailchimpSegmentRepository;
    private $translator;

    public function __construct(MailchimpSegmentRepository $mailchimpSegmentRepository, TranslatorInterface $translator)
    {
        $this->mailchimpSegmentRepository = $mailchimpSegmentRepository;
        $this->translator = $translator;
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
        if (!$referentTag = $filter->getReferentTag()) {
            throw new \InvalidArgumentException('There should be 1 selected tag for this campaign.');
        }

        $segmentLabels = [
            $referentTag->getCode(),
            $filter->getMandate(),
            $filter->getPoliticalFunction(),
            $filter->getLabel(),
            $filter->getUserListDefinition(),
        ];

        $filters = [];
        foreach ($segmentLabels as $segmentLabel) {
            if (!$segmentLabel) {
                continue;
            }

            $filters[] = $this->getSegment($this->translateTag($segmentLabel));
        }

        return [$this->buildConditions($filters)];
    }

    /**
     * @param MailchimpSegment[]|array $mailchimpSegments
     *
     * @return array[]|array
     */
    private function buildConditions(array $mailchimpSegments): array
    {
        return array_map(function (MailchimpSegment $mailchimpSegment) {
            return $this->buildCondition($mailchimpSegment);
        }, $mailchimpSegments);
    }

    private function buildCondition(MailchimpSegment $mailchimpSegment): array
    {
        return [
            'type' => 'mailchimp_segment',
            'value' => $mailchimpSegment,
            'label' => $mailchimpSegment->getLabel(),
        ];
    }

    private function translateTag(string $key): string
    {
        $translated = $this->translator->trans($transKey = ElectedRepresentativeTagsBuilder::TRANSLATION_PREFIX.$key);

        return $translated !== $transKey ? $translated : $key;
    }

    private function getSegment(string $label): MailchimpSegment
    {
        return $this->findMailchimpSegment($label) ?? MailchimpSegment::createElectedRepresentativeSegment($label);
    }

    private function findMailchimpSegment(string $label): ?MailchimpSegment
    {
        return $this->mailchimpSegmentRepository->findOneForElectedRepresentative($label);
    }
}
