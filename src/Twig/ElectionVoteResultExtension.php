<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ElectionVoteResultExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_city_vote_result', [ElectionVoteResultRuntime::class, 'getCityVoteResult']),
            new TwigFunction('get_ministry_vote_result', [ElectionVoteResultRuntime::class, 'getMinistryVoteResult']),
            new TwigFunction('get_aggregated_city_results', [ElectionVoteResultRuntime::class, 'getAggregatedCityResults']),
        ];
    }
}
