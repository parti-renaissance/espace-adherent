<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Utils\AreaUtils;

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
            'type' => 'text_merge',
            'value' => $inseeCode,
            'label' => $city['name'] ?? $inseeCode,
        ];

        if ($filter->getContactAdherents() && AreaUtils::INSEE_CODE_ANNECY === $inseeCode) {
            foreach (AreaUtils::INSEE_CODES_ATTACHED_TO_ANNECY as $inseeCodeAttachedToAnnecy) {
                $city = FranceCitiesBundle::getCityDataFromInseeCode($inseeCodeAttachedToAnnecy);

                $filters[] = [
                    'type' => 'text_merge',
                    'value' => $inseeCodeAttachedToAnnecy,
                    'label' => $city['name'] ?? $inseeCodeAttachedToAnnecy,
                ];
            }
        }

        return $filters;
    }
}
