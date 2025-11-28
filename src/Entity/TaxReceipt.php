<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaxReceiptRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TaxReceiptRepository::class)]
class TaxReceipt
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Donator::class, inversedBy: 'taxReceipts')]
    public Donator $donator;

    #[Groups(['tax_receipt:list'])]
    #[ORM\Column]
    public string $label;

    #[ORM\Column]
    public string $fileName;

    public function __construct(Donator $donator, string $label, string $filePath)
    {
        $this->uuid = Uuid::uuid4();
        $this->label = $label;
        $this->donator = $donator;
        $this->fileName = $filePath;
    }

    public function getFilePath(): string
    {
        return \sprintf('/files/tax_receipts/%s/%s', $this->donator->getIdentifier(), $this->fileName);
    }
}
