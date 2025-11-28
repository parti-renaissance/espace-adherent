<?php

declare(strict_types=1);

namespace App\Twig;

use App\CmsBlock\CmsBlockManager;
use Twig\Extension\RuntimeExtensionInterface;

class CmsBlockRuntime implements RuntimeExtensionInterface
{
    private CmsBlockManager $cmsBlockManager;

    public function __construct(CmsBlockManager $cmsBlockManager)
    {
        $this->cmsBlockManager = $cmsBlockManager;
    }

    public function getCmsBlockContent(string $name): ?string
    {
        return $this->cmsBlockManager->getContent($name);
    }
}
