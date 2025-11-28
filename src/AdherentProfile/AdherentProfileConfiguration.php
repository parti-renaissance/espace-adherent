<?php

declare(strict_types=1);

namespace App\AdherentProfile;

use App\Entity\ActivityAreaEnum;
use App\Entity\JobEnum;
use App\Entity\SubscriptionType;
use App\Membership\ActivityPositionsEnum;
use App\Repository\SubscriptionTypeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdherentProfileConfiguration
{
    private $translator;
    private $subscriptionTypeRepository;
    private $adherentInterests;

    public function __construct(
        TranslatorInterface $translator,
        SubscriptionTypeRepository $subscriptionTypeRepository,
        array $adherentInterests,
    ) {
        $this->translator = $translator;
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
        $this->adherentInterests = $adherentInterests;
    }

    public function build(): array
    {
        return [
            'interests' => array_map(function (string $code, string $label) {
                return [
                    'code' => $code,
                    'label' => $label,
                ];
            }, array_keys($this->adherentInterests), $this->adherentInterests),
            'subscription_types' => array_map(function (SubscriptionType $subscriptionType) {
                return [
                    'code' => $subscriptionType->getCode(),
                    'label' => $subscriptionType->getLabel(),
                ];
            }, $this->subscriptionTypeRepository->findAll()),
            'positions' => array_map(function (string $label, string $code) {
                return [
                    'code' => $code,
                    'label' => $this->translator->trans($label),
                ];
            }, array_keys(ActivityPositionsEnum::CHOICES), ActivityPositionsEnum::CHOICES),
            'jobs' => JobEnum::JOBS,
            'activity_area' => ActivityAreaEnum::ACTIVITIES,
        ];
    }
}
