<?php

namespace App\Entity;

use App\Deputy\DeputyMessage;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeputyManagedUsersMessageRepository")
 */
class DeputyManagedUsersMessage extends ManagedUsersMessage
{
    /**
     * @var District
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\District")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $district;

    public function __construct(
        UuidInterface $uuid,
        Adherent $from,
        string $subject,
        string $content,
        District $district,
        int $offset = 0
    ) {
        $this->uuid = $uuid;
        $this->from = $from;
        $this->subject = $subject;
        $this->content = $content;
        $this->district = $district;
        $this->offset = $offset;
    }

    public static function createFromMessage(DeputyMessage $message): self
    {
        return new self(
            $message->getUuid(),
            $message->getFrom(),
            $message->getSubject(),
            $message->getContent(),
            $message->getDistrict()
        );
    }

    public function getDistrict(): District
    {
        return $this->district;
    }

    public function setDistrict(District $district): void
    {
        $this->district = $district;
    }
}
