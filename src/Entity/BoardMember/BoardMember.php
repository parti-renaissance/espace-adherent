<?php

namespace AppBundle\Entity\BoardMember;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="board_member")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class BoardMember
{
    const AREA_FRANCE_METROPOLITAN = 'metropolitan';
    const AREA_OVERSEAS_FRANCE = 'overseas';
    const AREA_ABROAD = 'abroad';

    const AREAS_CHOICES = [
        'board_member.area.metropolitan' => self::AREA_FRANCE_METROPOLITAN,
        'board_member.area.overseas' => self::AREA_OVERSEAS_FRANCE,
        'board_member.area.abroad' => self::AREA_ABROAD,
    ];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Adherent", inversedBy="boardMember")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="board_member.area.invalid_choice", groups={"elections"})
     * @Assert\Choice(
     *      callback={"AppBundle\Entity\BoardMember\BoardMember", "getAreas"},
     *      message="board_member.area.invalid_choice",
     *      strict=true
     * )
     */
    private $area;

    /**
     * @var Role[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\BoardMember\Role", inversedBy="boardMembers", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="board_member_roles",
     *     joinColumns={
     *         @ORM\JoinColumn(name="board_member_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     * @ORM\OrderBy({"maleName"="ASC"})
     * @Assert\NotNull
     */
    private $roles;

    public function getId()
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent = null)
    {
        $this->adherent = $adherent;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function setArea(string $area)
    {
        $this->area = $area;
    }

    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function replaceRole(Role $actual, Role $new): void
    {
        $this->removeRole($actual);
        $this->addRole($new);
    }

    public function removeRole(Role $role): void
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * @return Role[]|Collection|iterable
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    public function setRoles(iterable $roles): void
    {
        $this->roles = $roles;
    }

    public static function getAreas(): array
    {
        return [
            self::AREA_FRANCE_METROPOLITAN,
            self::AREA_OVERSEAS_FRANCE,
            self::AREA_ABROAD,
        ];
    }
}
