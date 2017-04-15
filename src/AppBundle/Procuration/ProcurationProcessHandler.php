<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailjet\MailjetService;
use Doctrine\Common\Persistence\ObjectManager;

class ProcurationProcessHandler
{
    private $manager;
    private $mailjet;
    private $factory;

    public function __construct(ObjectManager $manager, MailjetService $mailjet, ProcurationProxyMessageFactory $factory)
    {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->factory = $factory;
    }

    public function process(Adherent $procurationManager, ProcurationRequest $request, ProcurationProxy $proxy)
    {
        $request->process($proxy, $procurationManager);

        $this->manager->persist($request);
        $this->manager->persist($proxy);
        $this->manager->flush();

        $this->mailjet->sendMessage($this->factory->createProxyFoundMessage($procurationManager, $request, $proxy));
    }

    public function unprocess(?Adherent $procurationManager, ProcurationRequest $request)
    {
        $proxy = $request->getFoundProxy();

        $request->unprocess();

        $this->manager->persist($request);
        $this->manager->persist($proxy);
        $this->manager->flush();

        if ($proxy) {
            $this->mailjet->sendMessage($this->factory->createProxyCancelledMessage($request, $proxy, $procurationManager));
        }
    }
}
