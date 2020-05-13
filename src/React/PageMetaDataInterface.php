<?php

namespace App\React;

interface PageMetaDataInterface
{
    /**
     * Title to use in the <title> HTML tag.
     */
    public function getTitle(): ?string;

    public function getDescription(): ?string;

    public function getImageWidth(): ?int;

    public function getImageHeight(): ?int;

    public function getImageUrl(): ?string;
}
