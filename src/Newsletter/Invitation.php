<?php

namespace AppBundle\Newsletter;

use Symfony\Component\Validator\Constraints as Assert;

class Invitation
{
    /**
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    public $firstName = '';

    /**
     * @Assert\Type("string")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     minMessage="common.first_name.min_length",
     *     maxMessage="common.first_name.max_length"
     * )
     */
    public $lastName = '';

    /**
     * @Assert\Type("array")
     * @Assert\Count(min=1, minMessage="newsletter.guests.min")
     * @Assert\All({
     *     @Assert\Email(message="common.email.invalid"),
     *     @Assert\NotBlank,
     *     @Assert\Length(max=255, maxMessage="common.email.max_length")
     * })
     */
    public $guests = [];

    public function filter()
    {
        $this->guests = array_filter($this->guests);
    }
}
