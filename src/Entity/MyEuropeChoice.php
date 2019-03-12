<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MyEuropeChoiceRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class MyEuropeChoice extends InteractiveChoice
{
    const MAIL_INTRODUCTION_KEY = 'S00C01';
    const MAIL_CONCLUSION_KEY = 'S00C02';
    const MAIL_COMMON_KEY = 'S00C03';

    const STEP_NOT_VISIBLE = 'my_europe.not_visible';
    const STEP_FRIEND_CASES = 'my_europe.friend_cases';
    const STEP_FRIEND_APPRECIATIONS = 'my_europe.friend_appreciations';

    const STEPS = [
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
