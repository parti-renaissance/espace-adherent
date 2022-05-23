<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\FranceCities\FranceCities;
use App\Utils\AreaUtils;

class MunicipalChiefMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof MunicipalChiefAdherentMessage && $message->getFilter() instanceof MunicipalChiefFilter;
    }

    /**
     * @param AdherentMessageFilterInterface|MunicipalChiefFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $inseeCode = $filter->getInseeCode();
        $city = $this->franceCities->getCityByInseeCode($inseeCode);

        $filters = [
            [
                [
                    'type' => self::TEXT_MERGE,
                    'value' => $inseeCode,
                    'label' => $city ? $city->getName() : $inseeCode,
                ],
            ],
        ];

        if (
            ($filter->getContactAdherents() || $filter->getContactNewsletter())
            && AreaUtils::INSEE_CODE_ANNECY === $inseeCode
        ) {
            foreach (AreaUtils::INSEE_CODES_ATTACHED_TO_ANNECY as $inseeCodeAttachedToAnnecy) {
                $city = $this->franceCities->getCityByInseeCode($inseeCodeAttachedToAnnecy);

                $filters[] = [
                    [
                        'type' => self::TEXT_MERGE,
                        'value' => $inseeCodeAttachedToAnnecy,
                        'label' => $city ? $city->getName() : $inseeCodeAttachedToAnnecy,
                    ],
                ];
            }
        }

        return $filters;
    }
}
