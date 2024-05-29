<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'adherent_message_filters')]
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
abstract class AbstractAdherentFilter implements SegmentFilterInterface
{
    use EntityTimestampableTrait;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $synchronized = false;

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function reset(): void
    {
    }
}
