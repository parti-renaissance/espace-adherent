<?php

namespace App\Controller;

use App\Entity\Mooc\AttachmentFile;
use App\Storage\FileRequestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mooc")
 */
class MoocController extends Controller
{
    /**
     * @Route("/file/{slug}.{extension}", name="mooc_get_file", methods={"GET"})
     * @Cache(maxage=900, smaxage=900)
     */
    public function getFile(FileRequestHandler $fileRequestHandler, AttachmentFile $file): Response
    {
        return $fileRequestHandler->createResponse($file);
    }
}
