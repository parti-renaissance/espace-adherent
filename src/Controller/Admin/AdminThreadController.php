<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\IdeasWorkshop\RedirectToReportListRouteTrait;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\IdeasWorkshop\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/ideasworkshop-thread/{uuid}")
 * @Security("has_role('ROLE_ADMIN_IDEAS_WORKSHOP')")
 * @Entity("thread", expr="repository.findOneByUuid(uuid, true)")
 */
class AdminThreadController extends Controller
{
    use RedirectToReportListRouteTrait;

    /**
     * Moderates a thread.
     *
     * @Route("/disable", methods={"GET"}, name="app_admin_thread_disable")
     */
    public function disableAction(Thread $thread, ObjectManager $manager, EventDispatcherInterface $dispatcher): Response
    {
        $thread->disable();

        $dispatcher->dispatch(Events::THREAD_DISABLE, new GenericEvent($thread));

        $manager->flush();
        $this->addFlash('sonata_flash_success', sprintf('Le fil de discussion « %s » a été modéré avec succès.', $thread->getId()));

        return $this->redirectToRoute('admin_app_ideasworkshop_thread_list');
    }

    /**
     * Enable a thread.
     *
     * @Route("/enable", methods={"GET"}, name="app_admin_thread_enable")
     */
    public function enableAction(Thread $thread, ObjectManager $manager, EventDispatcherInterface $dispatcher): Response
    {
        $thread->enable();

        $dispatcher->dispatch(Events::THREAD_ENABLE, new GenericEvent($thread));

        $manager->flush();
        $this->addFlash('sonata_flash_success', sprintf('Le fil de discussion « %s » a été activé avec succès.', $thread->getId()));

        return $this->redirectToRoute('admin_app_ideasworkshop_thread_list');
    }
}
