<?php

declare(strict_types=1);

namespace App\Utils;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class HttpUtils
{
    public static function createResponse(
        FilesystemOperator $storage,
        string $filePath,
        ?string $name = null,
        ?string $mimeType = null,
        bool $forceDownload = true,
    ): Response {
        if (!$storage->has($filePath)) {
            throw new NotFoundHttpException('No file found');
        }

        $response = new Response($storage->read($filePath), Response::HTTP_OK, ['Content-Type' => $mimeType ?? $storage->mimeType($filePath)]);

        if ($forceDownload) {
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $name ?? pathinfo($filePath, \PATHINFO_FILENAME)
            ));
        }

        return $response;
    }
}
