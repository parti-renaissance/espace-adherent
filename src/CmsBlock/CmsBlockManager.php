<?php

namespace App\CmsBlock;

use App\Repository\CmsBlockRepository;

class CmsBlockManager
{
    private $cmsBlockRepository;

    public function __construct(CmsBlockRepository $cmsBlockRepository)
    {
        $this->cmsBlockRepository = $cmsBlockRepository;
    }

    public function getContent(string $cmsBlockName): ?string
    {
        return $this->cmsBlockRepository->getContentByName($cmsBlockName);
    }
}
