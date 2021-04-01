<?php

namespace App\Entity\Poll;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get": {
 *             "path": "/polls",
 *             "method": "GET",
 *         }
 *     },
 *     attributes={
 *         "normalization_context": {"groups": {"poll_read"}},
 *         "order": {"finishAt": "ASC"}
 *     }
 * )
 *
 * @ORM\Entity
 */
class NationalPoll extends Poll
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $administrator;

    public function __construct(
        Administrator $administrator,
        UuidInterface $uuid = null,
        string $question = null,
        \DateTimeInterface $finishAt = null
    ) {
        parent::__construct($uuid, $question, $finishAt, true);

        $this->administrator = $administrator;
    }

    public function setAdministrator(Administrator $administrator): void
    {
        $this->administrator = $administrator;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function getType(): string
    {
        return PollTypeEnum::NATIONAL;
    }
}
