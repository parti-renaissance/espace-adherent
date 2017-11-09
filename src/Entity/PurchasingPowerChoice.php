<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PurchasingPowerChoiceRepository")
 */
class PurchasingPowerChoice extends InteractiveChoice
{
    const MAIL_INTRODUCTION_KEY = 'S00C01';
    const MAIL_CONCLUSION_KEY = 'S00C02';
    const MAIL_COMMON_KEY = 'S00C03';

    const STEP_NOT_VISIBLE = 'purchasing_power.not_visible';
    const STEP_FRIEND_PROFESSIONAL_POSITION = 'purchasing_power.friend_professional_position';
    const STEP_FRIEND_CASES = 'purchasing_power.friend_cases';
    const STEP_FRIEND_APPRECIATIONS = 'purchasing_power.friend_appreciations';

    const STEPS = [
        self::STEP_NOT_VISIBLE => 0,
        self::STEP_FRIEND_PROFESSIONAL_POSITION => 1,
        self::STEP_FRIEND_CASES => 2,
        self::STEP_FRIEND_APPRECIATIONS => 3,
    ];

    public static function getStepsOrderForEmail(): array
    {
        return [
            self::STEP_FRIEND_PROFESSIONAL_POSITION,
            self::STEP_FRIEND_APPRECIATIONS,
            self::STEP_FRIEND_CASES,
        ];
    }
}
