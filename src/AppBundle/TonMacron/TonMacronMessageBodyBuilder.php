<?php

namespace AppBundle\TonMacron;

use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Repository\TonMacronChoiceRepository;

class TonMacronMessageBodyBuilder
{
    private $twig;
    private $repository;

    public function __construct(
        \Twig_Environment $twig,
        TonMacronChoiceRepository $repository
    ) {
        $this->twig = $twig;
        $this->repository = $repository;
    }

    public function buildMessageBody(TonMacronFriendInvitation $invitation): string
    {
        return $this->twig->render('campaign/ton_macron.html.twig', [
            'introduction' => $this->repository->findMailIntroduction(),
            'conclusion' => $this->repository->findMailConclusion(),
            'invitation' => $invitation,
        ]);
    }
}
