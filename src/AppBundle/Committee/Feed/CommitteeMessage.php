<?php

namespace AppBundle\Committee\Feed;

use Symfony\Component\Validator\Constraints as Assert;

class CommitteeMessage
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=10,
     *     max=1500,
     *     minMessage="committee.message.min_length",
     *     maxMessage="committee.message.max_length",
     * )
     */
    public $content;
}
