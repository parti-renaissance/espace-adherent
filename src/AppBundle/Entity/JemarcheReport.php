<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jemarche_reports")
 * @ORM\Entity()
 */
class JemarcheReport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\NotBlank
     */
    private $actionType;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $convincedContacts;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $almostConvincedContacts;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $notConvincedContacts;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $publicReactions;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     *
     * @Assert\NotBlank
     */
    private $organizer;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set actionType.
     *
     * @param string $actionType
     *
     * @return JemarcheReport
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get actionType.
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set convincedContacts.
     *
     * @param string $convincedContacts
     *
     * @return JemarcheReport
     */
    public function setConvincedContacts($convincedContacts)
    {
        $this->convincedContacts = $convincedContacts;

        return $this;
    }

    /**
     * Get convincedContacts.
     *
     * @return string
     */
    public function getConvincedContacts()
    {
        return $this->convincedContacts;
    }

    /**
     * Set almostConvincedContacts.
     *
     * @param string $almostConvincedContacts
     *
     * @return JemarcheReport
     */
    public function setAlmostConvincedContacts($almostConvincedContacts)
    {
        $this->almostConvincedContacts = $almostConvincedContacts;

        return $this;
    }

    /**
     * Get almostConvincedContacts.
     *
     * @return string
     */
    public function getAlmostConvincedContacts()
    {
        return $this->almostConvincedContacts;
    }

    /**
     * Set notConvincedContacts.
     *
     * @param string $notConvincedContacts
     *
     * @return JemarcheReport
     */
    public function setNotConvincedContacts($notConvincedContacts)
    {
        $this->notConvincedContacts = $notConvincedContacts;

        return $this;
    }

    /**
     * Get notConvincedContacts.
     *
     * @return string
     */
    public function getNotConvincedContacts()
    {
        return $this->notConvincedContacts;
    }

    /**
     * Set publicReactions.
     *
     * @param string $publicReactions
     *
     * @return JemarcheReport
     */
    public function setPublicReactions($publicReactions)
    {
        $this->publicReactions = $publicReactions;

        return $this;
    }

    /**
     * Get publicReactions.
     *
     * @return string
     */
    public function getPublicReactions()
    {
        return $this->publicReactions;
    }

    /**
     * Set organizer.
     *
     * @param string $organizer
     *
     * @return JemarcheReport
     */
    public function setOrganizer($organizer)
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * Get organizer.
     *
     * @return string
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }
}

