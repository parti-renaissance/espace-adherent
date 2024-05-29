<?php

namespace App\Newsletter;

use Symfony\Component\Validator\Constraints as Assert;

class Invitation
{
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, minMessage: 'common.first_name.min_length', maxMessage: 'common.first_name.max_length')]
    public $firstName = '';

    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50, minMessage: 'common.last_name.min_length', maxMessage: 'common.last_name.max_length')]
    public $lastName = '';

    /**
     * @Assert\All({
     *     @Assert\Email(message="common.email.invalid"),
     *     @Assert\NotBlank,
     *     @Assert\Length(max=255, maxMessage="common.email.max_length")
     * })
     */
    #[Assert\Type('array')]
    #[Assert\Count(min: 1, minMessage: 'newsletter.guests.min')]
    public $guests = [];

    public function filter()
    {
        $this->guests = array_filter($this->guests);
    }
}
