<?php

namespace App\Content;

use App\Entity\CustomSearchResult;

class CustomSearchResultFactory
{
    public function createFromArray(array $data): CustomSearchResult
    {
        $searchResult = new CustomSearchResult();
        $searchResult->setTitle($data['title']);
        $searchResult->setUrl($data['url']);
        $searchResult->setDescription($data['description']);
        $searchResult->setMedia($data['media'] ?? null);
        $searchResult->setDisplayMedia(false);
        $searchResult->setKeywords($data['keywords'] ?? '');

        return $searchResult;
    }
}
