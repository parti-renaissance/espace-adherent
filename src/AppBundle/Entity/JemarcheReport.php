<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JemarcheReport
 *
 * @ORM\Table(name="jemarche_report")
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
     * @ORM\Column(name="actionType", type="string", length=50)
     */
    private $actionType;

    /**
     * @var string
     *
     * @ORM\Column(name="convincedContact", type="text")
     */
    private $convincedContact;

    /**
     * @var string
     *
     * @ORM\Column(name="almostConvincedContact", type="text")
     */
    private $almostConvincedContact;

    /**
     * @var string
     *
     * @ORM\Column(name="notConvicedContact", type="text")
     */
    private $notConvicedContact;

    /**
     * @var string
     *
     * @ORM\Column(name="publicReaction", type="text")
     */
    private $publicReaction;

    /**
     * @var string
     *
     * @ORM\Column(name="organizer", type="string", length=50)
     */
    private $organizer;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set actionType
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
     * Get actionType
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set convincedContact
     *
     * @param string $convincedContact
     *
     * @return JemarcheReport
     */
    public function setConvincedContact($convincedContact)
    {
        $this->convincedContact = $convincedContact;

        return $this;
    }

    /**
     * Get convincedContact
     *
     * @return string
     */
    public function getConvincedContact()
    {
        return $this->convincedContact;
    }

    /**
     * Set almostConvincedContact
     *
     * @param string $almostConvincedContact
     *
     * @return JemarcheReport
     */
    public function setAlmostConvincedContact($almostConvincedContact)
    {
        $this->almostConvincedContact = $almostConvincedContact;

        return $this;
    }

    /**
     * Get almostConvincedContact
     *
     * @return string
     */
    public function getAlmostConvincedContact()
    {
        return $this->almostConvincedContact;
    }

    /**
     * Set notConvicedContact
     *
     * @param string $notConvicedContact
     *
     * @return JemarcheReport
     */
    public function setNotConvicedContact($notConvicedContact)
    {
        $this->notConvicedContact = $notConvicedContact;

        return $this;
    }

    /**
     * Get notConvicedContact
     *
     * @return string
     */
    public function getNotConvicedContact()
    {
        return $this->notConvicedContact;
    }

    /**
     * Set publicReaction
     *
     * @param string $publicReaction
     *
     * @return JemarcheReport
     */
    public function setPublicReaction($publicReaction)
    {
        $this->publicReaction = $publicReaction;

        return $this;
    }

    /**
     * Get publicReaction
     *
     * @return string
     */
    public function getPublicReaction()
    {
        return $this->publicReaction;
    }

    /**
     * Set organizer
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
     * Get organizer
     *
     * @return string
     */
    public function getOrganizer()
    {
        return $this->organizer;
    }
}

