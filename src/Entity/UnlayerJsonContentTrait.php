<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait UnlayerJsonContentTrait
{
    /**
     * JSON representation of unlayer content (using by Unlayer JS lib)
     *
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups(['message_write', 'message_read_content', 'department_site_read', 'department_site_write', 'email_template_read', 'email_template_write'])]
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
