<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MyEuropeChoiceRepository")
 */
class MyEuropeChoice extends InteractiveChoice
{
    public const MAIL_INTRODUCTION_KEY = 'S00C01';
    public const MAIL_CONCLUSION_KEY = 'S00C02';
    public const MAIL_COMMON_KEY = 'S00C03';

    public const STEP_NOT_VISIBLE = 'my_europe.not_visible';
    public const STEP_FRIEND_CASES = 'my_europe.friend_cases';
    public const STEP_FRIEND_APPRECIATIONS = 'my_europe.friend_appreciations';

    public const STEPS = [
        self::STEP_NOT_VISIBLE => 0,
        self::STEP_FRIEND_CASES => 1,
        self::STEP_FRIEND_APPRECIATIONS => 2,
    ];

    public static function getStepsOrderForEmail(): array
    {
        return [
            self::STEP_FRIEND_CASES,
            self::STEP_FRIEND_APPRECIATIONS,
        ];
    }
}
