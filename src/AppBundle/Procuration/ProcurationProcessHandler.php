<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\ProcurationProxyCancelledMessage;
use AppBundle\Mailjet\Message\ProcurationProxyFoundMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProcurationProcessHandler
{
    private $manager;
    private $mailjet;
    private $router;

    public function __construct(ObjectManager $manager, MailjetService $mailjet, UrlGeneratorInterface $router)
    {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->router = $router;
    }

    public function process(Adherent $procurationManager, ProcurationRequest $request, ProcurationProxy $proxy)
    {
        $request->process($proxy);
        $proxy->setFoundRequest($request);

        $this->manager->persist($request);
        $this->manager->persist($proxy);
        $this->manager->flush();

        $this->mailjet->sendMessage(ProcurationProxyFoundMessage::create(
            $procurationManager,
            $request,
            $proxy,
            $this->router->generate('app_procuration_my_request', [
                'id' => $request->getId(),
                'token' => $request->generatePrivateToken(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    public function unprocess(Adherent $procurationManager, ProcurationRequest $request)
    {
        $proxy = $request->getFoundProxy();

        $request->unprocess();
        $proxy->setFoundRequest(null);

        $this->manager->persist($request);
        $this->manager->persist($proxy);
        $this->manager->flush();

        if ($proxy) {
            $this->mailjet->sendMessage(ProcurationProxyCancelledMessage::create($procurationManager, $request, $proxy));
        }
    }
}
