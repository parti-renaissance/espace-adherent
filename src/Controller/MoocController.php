<?php

namespace App\Controller;

use App\Entity\Mooc\AttachmentFile;
use App\Utils\HttpUtils;
use League\Flysystem\FilesystemOperator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mooc')]
class MoocController extends AbstractController
{
    #[Cache(maxage: 900, smaxage: 900)]
    #[Route(path: '/file/{slug}.{extension}', name: 'mooc_get_file', methods: ['GET'])]
    public function getFile(AttachmentFile $file, FilesystemOperator $defaultStorage): Response
    {
        return HttpUtils::createResponse($defaultStorage, $file->getPath(), $file->getSlug().'.'.$file->getExtension());
    }
}
