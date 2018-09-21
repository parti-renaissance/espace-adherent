<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Mooc\AttachmentFile;
use AppBundle\Storage\FileRequestHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/mooc")
 */
class MoocController extends Controller
{
    /**
     * @Route("/file/{slug}.{extension}", name="mooc_get_file")
     * @Method("GET")
     * @Cache(maxage=900, smaxage=900)
     */
    public function getFile(FileRequestHandler $fileRequestHandler, AttachmentFile $file): Response
    {
        return $fileRequestHandler->createResponse($file);
    }
}
