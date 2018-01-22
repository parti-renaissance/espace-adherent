<?php

namespace AppBundle\WebHook;

use AppBundle\Entity\WebHook\WebHook;
use AppBundle\Repository\WebHookRepository;
use Doctrine\ORM\EntityManagerInterface;

class WebHookManager
{
    private $webHookRepository;
    private $em;

    public function __construct(WebHookRepository $webHookRepository, EntityManagerInterface $em)
    {
        $this->webHookRepository = $webHookRepository;
        $this->em = $em;
    }

    public function getOrCreateWebHook(Event $event): WebHook
    {
        if (!$webHook = $this->webHookRepository->findOneByEvent($event)) {
            $webHook = new WebHook($event);

            $this->em->persist($webHook);
            $this->em->flush($webHook);
        }

        return $webHook;
    }
}
