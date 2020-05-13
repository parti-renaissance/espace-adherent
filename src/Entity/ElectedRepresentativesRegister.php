<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ElectedRepresentativesRegisterRepository")
 */
class ElectedRepresentativesRegister
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $departmentId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $communeId;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(name="adherent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $adherent;

    /**
     * @ORM\Column(nullable=true)
     */
    private $typeElu;

    /**
     * @ORM\Column(nullable=true)
     */
    private $dpt;

    /**
     * @ORM\Column(nullable=true)
     */
    private $dptNom;

    /**
     * @ORM\Column(nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(nullable=true)
     */
    private $genre;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateNaissance;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $codeProfession;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nomProfession;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $dateDebutMandat;

    /**
     * @ORM\Column(nullable=true)
     */
    private $nomFonction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDebutFonction;

    /**
     * @ORM\Column(nullable=true)
     */
    private $nuancePolitique;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $identificationElu;

    /**
     * @ORM\Column(nullable=true)
     */
    private $nationaliteElu;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $epciSiren;

    /**
     * @ORM\Column(nullable=true)
     */
    private $epciNom;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $communeDpt;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $communeCode;

    /**
     * @ORM\Column(nullable=true)
     */
    private $communeNom;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $communePopulation;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $cantonCode;

    /**
     * @ORM\Column(nullable=true)
     */
    private $cantonNom;

    /**
     * @ORM\Column(nullable=true)
     */
    private $regionCode;

    /**
     * @ORM\Column(nullable=true)
     */
    private $regionNom;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $euroCode;

    /**
     * @ORM\Column(nullable=true)
     */
    private $euroNom;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $circoLegisCode;

    /**
     * @ORM\Column(nullable=true)
     */
    private $circoLegisNom;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $infosSupp;

    /**
     * @ORM\Column(type="string", length=36, nullable=true)
     */
    private $uuid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbParticipationEvents;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    private $adherentUuid;

    public static function create(
        ?int $departmentId,
        ?int $communeId,
        ?Adherent $adherent,
        ?string $typeElu,
        ?string $dpt,
        ?string $dptNom,
        ?string $nom,
        ?string $prenom,
        ?string $genre,
        ?\DateTime $dateNaissance,
        ?int $codeProfession,
        ?string $nomProfession,
        ?string $dateDebutMandat,
        ?string $nomFonction,
        ?\DateTime $dateDebutFonction,
        ?string $nuancePolitique,
        ?int $identificationElu,
        ?string $nationaliteElu,
        ?int $epciSiren,
        ?string $epciNom,
        ?int $communeDpt,
        ?int $communeCode,
        ?string $communeNom,
        ?int $communePopulation,
        ?int $cantonCode,
        ?string $cantonNom,
        ?string $regionCode,
        ?string $regionNom,
        ?int $euroCode,
        ?string $euroNom,
        ?int $circoLegisCode,
        ?string $circoLegisNom,
        ?string $infosSupp,
        ?string $uuid,
        ?int $nbParticipationEvents,
        ?string $adherentUuid
    ): self {
        $electedRepresentativesRegister = new self();
        $electedRepresentativesRegister->departmentId = $departmentId;
        $electedRepresentativesRegister->communeId = $communeId;
        $electedRepresentativesRegister->adherent = $adherent;
        $electedRepresentativesRegister->typeElu = $typeElu;
        $electedRepresentativesRegister->dpt = $dpt;
        $electedRepresentativesRegister->dptNom = $dptNom;
        $electedRepresentativesRegister->nom = $nom;
        $electedRepresentativesRegister->prenom = $prenom;
        $electedRepresentativesRegister->genre = $genre;
        $electedRepresentativesRegister->dateNaissance = $dateNaissance;
        $electedRepresentativesRegister->codeProfession = $codeProfession;
        $electedRepresentativesRegister->nomProfession = $nomProfession;
        $electedRepresentativesRegister->dateDebutMandat = $dateDebutMandat;
        $electedRepresentativesRegister->nomFonction = $nomFonction;
        $electedRepresentativesRegister->dateDebutFonction = $dateDebutFonction;
        $electedRepresentativesRegister->nuancePolitique = $nuancePolitique;
        $electedRepresentativesRegister->identificationElu = $identificationElu;
        $electedRepresentativesRegister->nationaliteElu = $nationaliteElu;
        $electedRepresentativesRegister->epciSiren = $epciSiren;
        $electedRepresentativesRegister->epciNom = $epciNom;
        $electedRepresentativesRegister->communeDpt = $communeDpt;
        $electedRepresentativesRegister->communeCode = $communeCode;
        $electedRepresentativesRegister->communeNom = $communeNom;
        $electedRepresentativesRegister->communePopulation = $communePopulation;
        $electedRepresentativesRegister->cantonCode = $cantonCode;
        $electedRepresentativesRegister->cantonNom = $cantonNom;
        $electedRepresentativesRegister->regionCode = $regionCode;
        $electedRepresentativesRegister->regionNom = $regionNom;
        $electedRepresentativesRegister->euroCode = $euroCode;
        $electedRepresentativesRegister->euroNom = $euroNom;
        $electedRepresentativesRegister->circoLegisCode = $circoLegisCode;
        $electedRepresentativesRegister->circoLegisNom = $circoLegisNom;
        $electedRepresentativesRegister->infosSupp = $infosSupp;
        $electedRepresentativesRegister->uuid = $uuid;
        $electedRepresentativesRegister->nbParticipationEvents = $nbParticipationEvents;
        $electedRepresentativesRegister->adherentUuid = $adherentUuid;

        return $electedRepresentativesRegister;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartmentId(): ?int
    {
        return $this->departmentId;
    }

    public function setDepartmentId(?int $departmentId): void
    {
        $this->departmentId = $departmentId;
    }

    public function getCommuneId(): ?int
    {
        return $this->communeId;
    }

    public function setCommuneId(?int $communeId): void
    {
        $this->communeId = $communeId;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): void
    {
        $this->adherent = $adherent;
        if (null !== $this->adherent) {
            $this->setAdherentUuid($this->adherent->getUuid()->toString());
        }
    }

    public function getTypeElu(): ?string
    {
        return $this->typeElu;
    }

    public function setTypeElu(?string $typeElu): void
    {
        $this->typeElu = $typeElu;
    }

    public function getDpt(): ?string
    {
        return $this->dpt;
    }

    public function setDpt(?string $dpt): void
    {
        $this->dpt = $dpt;
    }

    public function getDptNom(): ?string
    {
        return $this->dptNom;
    }

    public function setDptNom(?string $dptNom): void
    {
        $this->dptNom = $dptNom;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): void
    {
        $this->nom = $nom;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): void
    {
        $this->prenom = $prenom;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): void
    {
        $this->genre = $genre;
    }

    public function getDateNaissance(): ?\DateTime
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTime $dateNaissance): void
    {
        $this->dateNaissance = $dateNaissance;
    }

    public function getCodeProfession(): ?int
    {
        return $this->codeProfession;
    }

    public function setCodeProfession(?int $codeProfession): void
    {
        $this->codeProfession = $codeProfession;
    }

    public function getNomProfession(): ?string
    {
        return $this->nomProfession;
    }

    public function setNomProfession(?string $nomProfession): void
    {
        $this->nomProfession = $nomProfession;
    }

    public function getDateDebutMandat(): ?string
    {
        return $this->dateDebutMandat;
    }

    public function setDateDebutMandat(?string $dateDebutMandat): void
    {
        $this->dateDebutMandat = $dateDebutMandat;
    }

    public function getNomFonction(): ?string
    {
        return $this->nomFonction;
    }

    public function setNomFonction(?string $nomFonction): void
    {
        $this->nomFonction = $nomFonction;
    }

    public function getDateDebutFonction(): ?\DateTime
    {
        return $this->dateDebutFonction;
    }

    public function setDateDebutFonction(?\DateTime $dateDebutFonction): void
    {
        $this->dateDebutFonction = $dateDebutFonction;
    }

    public function getNuancePolitique(): ?string
    {
        return $this->nuancePolitique;
    }

    public function setNuancePolitique(?string $nuancePolitique): void
    {
        $this->nuancePolitique = $nuancePolitique;
    }

    public function getIdentificationElu(): ?int
    {
        return $this->identificationElu;
    }

    public function setIdentificationElu(?int $identificationElu): void
    {
        $this->identificationElu = $identificationElu;
    }

    public function getNationaliteElu(): ?string
    {
        return $this->nationaliteElu;
    }

    public function setNationaliteElu(?string $nationaliteElu): void
    {
        $this->nationaliteElu = $nationaliteElu;
    }

    public function getEpciSiren(): ?int
    {
        return $this->epciSiren;
    }

    public function setEpciSiren(?int $epciSiren): void
    {
        $this->epciSiren = $epciSiren;
    }

    public function getEpciNom(): ?string
    {
        return $this->epciNom;
    }

    public function setEpciNom(?string $epciNom): void
    {
        $this->epciNom = $epciNom;
    }

    public function getCommuneDpt(): ?int
    {
        return $this->communeDpt;
    }

    public function setCommuneDpt(?int $communeDpt): void
    {
        $this->communeDpt = $communeDpt;
    }

    public function getCommuneCode(): ?int
    {
        return $this->communeCode;
    }

    public function setCommuneCode(?int $communeCode): void
    {
        $this->communeCode = $communeCode;
    }

    public function getCommuneNom(): ?string
    {
        return $this->communeNom;
    }

    public function setCommuneNom(?string $communeNom): void
    {
        $this->communeNom = $communeNom;
    }

    public function getCommunePopulation(): ?int
    {
        return $this->communePopulation;
    }

    public function setCommunePopulation(?int $communePopulation): void
    {
        $this->communePopulation = $communePopulation;
    }

    public function getCantonCode(): ?int
    {
        return $this->cantonCode;
    }

    public function setCantonCode(?int $cantonCode): void
    {
        $this->cantonCode = $cantonCode;
    }

    public function getCantonNom(): ?string
    {
        return $this->cantonNom;
    }

    public function setCantonNom(?string $cantonNom): void
    {
        $this->cantonNom = $cantonNom;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function setRegionCode(?string $regionCode): void
    {
        $this->regionCode = $regionCode;
    }

    public function getRegionNom(): ?string
    {
        return $this->regionNom;
    }

    public function setRegionNom(?string $regionNom): void
    {
        $this->regionNom = $regionNom;
    }

    public function getEuroCode(): ?int
    {
        return $this->euroCode;
    }

    public function setEuroCode(?int $euroCode): void
    {
        $this->euroCode = $euroCode;
    }

    public function getEuroNom(): ?string
    {
        return $this->euroNom;
    }

    public function setEuroNom(?string $euroNom): void
    {
        $this->euroNom = $euroNom;
    }

    public function getCircoLegisCode(): ?int
    {
        return $this->circoLegisCode;
    }

    public function setCircoLegisCode(?int $circoLegisCode): void
    {
        $this->circoLegisCode = $circoLegisCode;
    }

    public function getCircoLegisNom(): ?string
    {
        return $this->circoLegisNom;
    }

    public function setCircoLegisNom(?string $circoLegisNom): void
    {
        $this->circoLegisNom = $circoLegisNom;
    }

    public function getInfosSupp(): ?string
    {
        return $this->infosSupp;
    }

    public function setInfosSupp(?string $infosSupp): void
    {
        $this->infosSupp = $infosSupp;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): void
    {
        $this->uuid = $uuid;
    }

    public function getNbParticipationEvents(): ?int
    {
        return $this->nbParticipationEvents;
    }

    public function setNbParticipationEvents(?int $nbParticipationEvents): void
    {
        $this->nbParticipationEvents = $nbParticipationEvents;
    }

    public function getAdherentUuid(): ?string
    {
        return $this->adherentUuid;
    }

    public function setAdherentUuid(?string $adherentUuid): void
    {
        $this->adherentUuid = $adherentUuid;
    }
}
