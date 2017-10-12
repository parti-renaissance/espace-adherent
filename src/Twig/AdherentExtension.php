<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adherent;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AdherentExtension extends AbstractExtension
{
    private $memberInterests;
    private $unregistrationReasons;

    public function __construct(array $interests, array $unregistrationReasons)
    {
        $this->memberInterests = $interests;
        $this->unregistrationReasons = $unregistrationReasons;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('member_interest_label', [$this, 'getMemberInterestLabel']),
            new TwigFunction('unregistration_reasons_label', [$this, 'getUnregistrationReasonsLabel']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('age', [$this, 'getAge']),
        ];
    }

    public function getMemberInterestLabel(string $interest)
    {
        if (!isset($this->memberInterests[$interest])) {
            return '';
        }

        return $this->memberInterests[$interest];
    }

    public function getUnregistrationReasonsLabel(string $reasons)
    {
        if (!isset($this->unregistrationReasons[$reasons])) {
            return '';
        }

        return $this->unregistrationReasons[$reasons];
    }

    public function getAge(Adherent $adherent)
    {
        return (new \DateTime())->diff($adherent->getBirthdate())->format('%Y');
    }
}
