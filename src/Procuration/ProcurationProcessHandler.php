<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailer\MailerService;
use Doctrine\Common\Persistence\ObjectManager;

class ProcurationProcessHandler
{
    private $manager;
    private $mailer;
    private $factory;

    public function __construct(ObjectManager $manager, MailerService $mailer, ProcurationProxyMessageFactory $factory)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->factory = $factory;
    }

    public function process(Adherent $procurationManager, ProcurationRequest $request, ProcurationProxy $proxy)
    {
        $request->process($proxy, $procurationManager);

        $this->manager->flush();

        $this->mailer->sendMessage($this->factory->createProxyFoundMessage($procurationManager, $request, $proxy));
    }

    public function unprocess(?Adherent $procurationManager, ProcurationRequest $request)
    {
        $proxy = $request->getFoundProxy();

        $request->unprocess();

        $this->manager->flush();

        if ($proxy) {
            $this->mailer->sendMessage($this->factory->createProxyCancelledMessage($request, $proxy, $procurationManager));
        }
    }
}
