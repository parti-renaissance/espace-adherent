<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentMessageExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_message_limit', [AdherentMessageRuntime::class, 'getMessageLimit']),
            new TwigFunction('get_sent_message_count', [AdherentMessageRuntime::class, 'getSentMessageCount']),
        ];
    }
}
