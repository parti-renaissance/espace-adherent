<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait UnlayerJsonContentTrait
{
    /**
     * JSON representation of unlayer content (using by Unlayer JS lib)
     */
    #[Groups(['message_write', 'message_read', 'message_read_content', 'department_site_read', 'department_site_write', 'email_template_read', 'email_template_write'])]
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $jsonContent = null;

    #[Assert\NotBlank]
    #[Groups(['department_site_read', 'department_site_write', 'message_write', 'message_read_content', 'email_template_read', 'email_template_write'])]
    #[ORM\Column(type: 'text')]
    protected ?string $content = null;

    public function getJsonContent(): ?string
    {
        return $this->jsonContent;
    }

    public function setJsonContent(?string $jsonContent): void
    {
        $this->jsonContent = trim($jsonContent);
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = trim($content);
    }
}
