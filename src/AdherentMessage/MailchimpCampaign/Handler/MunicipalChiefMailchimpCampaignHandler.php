<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\Intl\FranceCitiesBundle;
use App\Utils\AreaUtils;

class MunicipalChiefMailchimpCampaignHandler extends AbstractMailchimpCampaignHandler
{
    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof MunicipalChiefAdherentMessage;
    }

    /**
     * @param AdherentMessageFilterInterface|MunicipalChiefFilter $filter
     */
    protected function getCampaignFilters(AdherentMessageFilterInterface $filter): array
    {
        $inseeCode = $filter->getInseeCode();
        $city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCode);

        $filters = [
            [
                [
                    'type' => 'text_merge',
                    'value' => $inseeCode,
                    'label' => $city['name'] ?? $inseeCode,
                ],
            ],
        ];

        if (
            ($filter->getContactAdherents() || $filter->getContactNewsletter())
            && AreaUtils::INSEE_CODE_ANNECY === $inseeCode
        ) {
            foreach (AreaUtils::INSEE_CODES_ATTACHED_TO_ANNECY as $inseeCodeAttachedToAnnecy) {
                $city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCodeAttachedToAnnecy);

                $filters[] = [
                    [
                        'type' => 'text_merge',
                        'value' => $inseeCodeAttachedToAnnecy,
                        'label' => $city['name'] ?? $inseeCodeAttachedToAnnecy,
                    ],
                ];
            }
        }

        return $filters;
    }
}
