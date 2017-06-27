<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentExtension extends AbstractExtension
{
    private $memberInterests;

    public function __construct(array $interests)
    {
        $this->memberInterests = $interests;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('member_interest_label', [$this, 'getMemberInterestLabel']),
        ];
    }

    public function getMemberInterestLabel(string $interest)
    {
        if (!isset($this->memberInterests[$interest])) {
            return '';
        }

        return $this->memberInterests[$interest];
    }
}
