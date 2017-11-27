<?php

namespace AppBundle\Interactive;

use AppBundle\Repository\InteractiveChoiceRepository;

class InteractiveMessageBodyBuilder
{
    private $twig;
    private $repository;

    public function __construct(
        \Twig_Environment $twig,
        InteractiveChoiceRepository $repository
    ) {
        $this->twig = $twig;
        $this->repository = $repository;
    }

    public function buildMessageBody(InteractiveProcessor $invitation): void
    {
        $invitation->messageContent = $this->twig->render('interactive/mail.html.twig', [
            'introduction' => $this->repository->findMailIntroduction(),
            'common' => $this->repository->findMailCommon(),
            'conclusion' => $this->repository->findMailConclusion(),
            'interactive' => $invitation,
        ]);
    }
}
