<?php

declare(strict_types=1);

namespace App\Riposte;

use App\Entity\Jecoute\Riposte;
use App\OpenGraph\OpenGraphFetcher;

class RiposteOpenGraphHandler
{
    private $openGraphFetcher;

    public function __construct(OpenGraphFetcher $openGraphFetcher)
    {
        $this->openGraphFetcher = $openGraphFetcher;
    }

    public function handle(Riposte $riposte): void
    {
        $riposte->clearOpenGraph();

        if (!$url = $riposte->getSourceUrl()) {
            return;
        }

        $openGraph = $this->openGraphFetcher->fetch($url);

        if (!empty($openGraph)) {
            $riposte->setOpenGraph($openGraph);
        }
    }
}
