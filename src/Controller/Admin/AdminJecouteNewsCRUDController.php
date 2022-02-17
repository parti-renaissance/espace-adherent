<?php

namespace App\Controller\Admin;

use App\Entity\Jecoute\News;
use App\Jecoute\NewsHandler;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

class AdminJecouteNewsCRUDController extends CRUDController
{
    public function pinAction(NewsHandler $newsHandler, EntityManagerInterface $entityManager): Response
    {
        /** @var News $news */
        $news = $this->admin->getSubject();

        $this->admin->checkAccess('pin', $news);

        $news->setPinned(!$news->isPinned());
        $entityManager->flush();

        $newsHandler->changePinned($news);

        return $this->redirectToList();
    }
}
