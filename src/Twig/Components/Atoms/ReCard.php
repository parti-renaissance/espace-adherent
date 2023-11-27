<?php

/** @see src/Twig/Components/Alert.php */

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReCard
{
    /**
     * @var 'outer'|'inner'
     */
    public string $variant = 'outer';
}
