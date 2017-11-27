<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InteractiveChoiceRepository")
 */
class InteractiveChoice extends AbstractInteractiveChoice
{
    const MAIL_INTRODUCTION_KEY = 'S00C01';
    const MAIL_CONCLUSION_KEY = 'S00C02';
    const MAIL_COMMON_KEY = 'S00C03';

    const STEP_NOT_VISIBLE = 'interactive.not_visible';
    const STEP_FRIEND_PROFESSIONAL_POSITION = 'interactive.friend_professional_position';
    const STEP_FRIEND_CASES = 'interactive.friend_cases';
    const STEP_FRIEND_APPRECIATIONS = 'interactive.friend_appreciations';

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
            self::STEP_FRIEND_CASES,
            self::STEP_FRIEND_APPRECIATIONS,
        ];
    }
}
