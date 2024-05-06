<?php

namespace App\Procuration\V2\Command;

use Symfony\Component\Validator\Constraints as Assert;

class ProxyCommand extends AbstractCommand
{
    /**
     * @Assert\Length(
     *     min=7,
     *     max=9
     * )
     * @Assert\Regex(pattern="/^[0-9]+$/i")
     */
    public ?string $electorNumber = null;

    /**
     * @Assert\Expression(
     *     expression="value >= 1 and ((!this.isFDE() and value <= 2) or (this.isFDE() and value <= 3))",
     *     message="procuration.proxy.slots.invalid"
     * )
     */
    public int $slots = 1;
}
