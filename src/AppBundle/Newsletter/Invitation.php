<?php

namespace AppBundle\Newsletter;

use Symfony\Component\Validator\Constraints as Assert;

class Invitation
{
    /**
     * @Assert\Type("string")
     * @Assert\NotBlank(message="common.first_name.not_blank")
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
     * @Assert\NotBlank(message="common.last_name.not_blank")
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
     *    @Assert\Email(message="common.email.invalid"),
     *    @Assert\NotBlank(message="common.email.not_blank")
     * })
     */
    public $guests = [];

    public function filter()
    {
        $this->guests = array_filter($this->guests);
    }
}
