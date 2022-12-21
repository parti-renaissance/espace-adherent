<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UnLayerJsonContentTrait
{
    /**
     * JSON representation of unlayer content (using by Unlayer JS lib)
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({
     *     "message_write",
     *     "message_read_content",
     *     "local_site_read",
     *     "local_site_read_list",
     *     "local_site_write",
     * })
     */
    protected ?string $jsonContent = null;

    public function getJsonContent(): ?string
    {
        return $this->jsonContent;
    }

    public function setJsonContent(?string $jsonContent): void
    {
        $this->jsonContent = $jsonContent;
    }
}
