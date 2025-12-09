<?php

declare(strict_types=1);

namespace App\Entity\AdherentMandate;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: SearchFilter::class, properties: ['adherent.uuid' => 'exact'])]
#[ApiResource(
    operations: [
        new Put(
            uriTemplate: '/elected_adherent_mandates/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
        ),
        new Delete(
            uriTemplate: '/elected_adherent_mandates/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
        ),
        new Post(uriTemplate: '/elected_adherent_mandates'),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['elected_mandate_read']],
    denormalizationContext: ['groups' => ['elected_mandate_write']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'elected_representative')"
)]
#[ORM\Entity(repositoryClass: ElectedRepresentativeAdherentMandateRepository::class)]
class ElectedRepresentativeAdherentMandate extends AbstractAdherentMandate
{
    #[Assert\Choice(choices: MandateTypeEnum::ALL)]
    #[Assert\NotBlank]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\Column]
    public string $mandateType;

    #[Assert\Length(max: 255)]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $delegation = null;

    #[Assert\Expression("value !== null or (value == null and this.mandateType === constant('App\\\\Adherent\\\\MandateTypeEnum::DEPUTE_EUROPEEN'))", message: 'Le périmètre géographique est obligatoire.')]
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    public ?Zone $zone = null;

    public static function create(
        ?UuidInterface $uuid,
        Adherent $adherent,
        string $mandateType,
        ?\DateTime $beginAt = null,
        ?\DateTime $finishAt = null,
        ?string $delegation = null,
        ?Zone $zone = null,
    ): self {
        $mandate = new self($adherent, null, $beginAt, $finishAt);
        $mandate->uuid = $uuid ?? Uuid::uuid4();
        $mandate->mandateType = $mandateType;
        $mandate->delegation = $delegation;
        $mandate->zone = $zone;

        return $mandate;
    }

    public function isLocal(): bool
    {
        return \in_array($this->mandateType, MandateTypeEnum::LOCAL_TYPES, true);
    }
}
