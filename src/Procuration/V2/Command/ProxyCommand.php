<?php

namespace App\Procuration\V2\Command;

use Symfony\Component\Validator\Constraints as Assert;

class ProxyCommand extends AbstractCommand
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=8,
     *     max=9
     * )
     * @Assert\Regex(pattern="/^[0-9]+$/i")
     */
    public ?string $electorNumber = null;

    /**
     * @Assert\Range(
     *     min=1,
     *     max=2
     * )
     */
    public int $slots = 1;
}
