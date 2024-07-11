<?php

namespace App\Newsletter;

use Symfony\Component\Validator\Constraints as Assert;

class Invitation
{
    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public $firstName = '';

    #[Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length')]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public $lastName = '';

    /**
     * @Assert\All({
     *     @Assert\Email(message="common.email.invalid"),
     *     @Assert\NotBlank,
     *     @Assert\Length(max=255, maxMessage="common.email.max_length")
     * })
     */
    #[Assert\Count(min: 1, minMessage: 'newsletter.guests.min')]
    #[Assert\Type('array')]
    public $guests = [];

    public function filter()
    {
        $this->guests = array_filter($this->guests);
    }
}
