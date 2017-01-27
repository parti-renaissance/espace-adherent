<?php

namespace AppBundle\Content;

use AppBundle\Entity\Page;

class PageFactory
{
    public function createFromArray(array $data): Page
    {
        $page = new Page();
        $page->setTitle($data['title']);
        $page->setSlug($data['slug']);
        $page->setDescription($data['description']);
        $page->setContent($data['content']);

        return $page;
    }
}
