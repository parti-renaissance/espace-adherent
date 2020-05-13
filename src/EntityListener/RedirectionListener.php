<?php

namespace App\EntityListener;

use App\Entity\Redirection;
use App\Redirection\Dynamic\RedirectionManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class RedirectionListener
{
    private $redirectionManager;

    /**
     * @var Redirection[]
     */
    private $redirectionsUpdated = [];

    public function __construct(RedirectionManager $redirectionManager)
    {
        $this->redirectionManager = $redirectionManager;
    }

    public function preUpdate(Redirection $redirection, PreUpdateEventArgs $preUpdateEventArgs): void
    {
        if ($preUpdateEventArgs->hasChangedField('to') || $preUpdateEventArgs->hasChangedField('from')) {
            $this->redirectionsUpdated[] = $redirection->getId();
        }
    }

    public function postRemove(Redirection $redirection): void
    {
        $this->redirectionManager->removeRedirectionFromCache($redirection->getFrom());
    }

    public function postUpdate(Redirection $redirection): void
    {
        if (\in_array($redirection->getId(), $this->redirectionsUpdated, true)) {
            $this->redirectionManager->refreshRedirectionCache($redirection);
        }
    }
}
