<?php

namespace App\Interactive;

use App\Repository\MyEuropeChoiceRepository;

class MyEuropeMessageBodyBuilder
{
    private $twig;
    private $repository;

    public function __construct(\Twig_Environment $twig, MyEuropeChoiceRepository $repository)
    {
        $this->twig = $twig;
        $this->repository = $repository;
    }

    public function buildMessageBody(MyEuropeProcessor $invitation): void
    {
        $invitation->messageContent = $this->twig->render('interactive/mail.html.twig', [
            'introduction' => $this->repository->findMailIntroduction(),
            'common' => $this->repository->findMailCommon(),
            'conclusion' => $this->repository->findMailConclusion(),
            'interactive' => $invitation,
        ]);
    }
}
