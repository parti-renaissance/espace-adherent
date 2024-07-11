<?php

namespace App\Entity\AdherentMandate;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ElectedRepresentativeAdherentMandateRepository::class)]
#[ApiResource(routePrefix: '/v3', attributes: ['normalization_context' => ['groups' => ['elected_mandate_read']], 'denormalization_context' => ['groups' => ['elected_mandate_write']], 'security' => "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"], itemOperations: ['put' => ['path' => '/elected_adherent_mandates/{uuid}', 'requirements' => ['uuid' => '%pattern_uuid%'], 'security' => "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"], 'delete' => ['path' => '/elected_adherent_mandates/{uuid}', 'requirements' => ['uuid' => '%pattern_uuid%'], 'security' => "is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'elected_representative')"]], collectionOperations: ['post' => ['path' => '/elected_adherent_mandates']])]
#[ApiFilter(SearchFilter::class, properties: ['adherent.uuid' => 'exact'])]
class ElectedRepresentativeAdherentMandate extends AbstractAdherentMandate
{
    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: MandateTypeEnum::ALL)]
    public string $mandateType;

    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\Column(nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $delegation = null;

    #[Groups(['elected_mandate_write', 'elected_mandate_read', 'adherent_elect_read'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Zone::class)]
    #[Assert\Expression("value !== null or (value == null and this.mandateType === constant('App\\\\Adherent\\\\MandateTypeEnum::DEPUTE_EUROPEEN'))", message: 'Le périmètre géographique est obligatoire.')]
    public ?Zone $zone = null;

    public static function create(
        ?UuidInterface $uuid,
        Adherent $adherent,
        string $mandateType,
        ?\DateTime $beginAt = null,
        ?\DateTime $finishAt = null,
        ?string $delegation = null,
        ?Zone $zone = null
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
