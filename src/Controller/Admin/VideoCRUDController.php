<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use App\Video\Transcoding\Command\RelaunchVideoTranscodingCommand;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class VideoCRUDController extends CRUDController
{
    public function relaunchAction(int $id, MessageBusInterface $messageBus): Response
    {
        /** @var Video|null $video */
        $video = $this->admin->getObject($id);

        if (!$video) {
            throw $this->createNotFoundException(\sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('relaunch', $video);

        if (VideoStatusEnum::FAILED !== $video->status || null === $video->originalPath) {
            $this->addFlash('sonata_flash_error', 'Ce transcodage ne peut pas être relancé.');

            return $this->redirect($this->admin->generateUrl('list'));
        }

        $messageBus->dispatch(new RelaunchVideoTranscodingCommand($video->getUuid()->toRfc4122()));
        $this->addFlash('sonata_flash_success', 'Le transcodage a été relancé.');

        return $this->redirect($this->admin->generateObjectUrl('show', $video));
    }
}
