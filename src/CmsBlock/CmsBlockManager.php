<?php

declare(strict_types=1);

namespace App\CmsBlock;

use App\Repository\CmsBlockRepository;
use Psr\Log\LoggerInterface;

class CmsBlockManager
{
    private $cmsBlockRepository;
    private $logger;

    public function __construct(CmsBlockRepository $cmsBlockRepository, LoggerInterface $logger)
    {
        $this->cmsBlockRepository = $cmsBlockRepository;
        $this->logger = $logger;
    }

    public function getContent(string $cmsBlockName): ?string
    {
        $content = $this->cmsBlockRepository->getContentByName($cmsBlockName);

        if (!$content) {
            $this->logger->error(\sprintf('No content found for CmsBlock with name "%s".', $cmsBlockName));
        }

        return $content;
    }
}
