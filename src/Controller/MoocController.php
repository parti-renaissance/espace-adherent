<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mooc\AttachmentFile;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/mooc")
 */
class MoocController extends Controller
{
    /**
     * @Route("/file/{uuid}", requirements={"uuid": "%pattern_uuid%"}, name="mooc_get_file")
     * @Method("GET")
     * @Cache(maxage=900, smaxage=900)
     */
    public function getFile(FilesystemInterface $filesystem, Slugify $slugify, AttachmentFile $file): Response
    {
        $response = new Response($filesystem->read($file->getPath()), Response::HTTP_OK, [
            'Content-Type' => $filesystem->getMimetype($file->getPath()),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $slugify->slugify($file->getTitle()).'.'.$file->getExtension()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
