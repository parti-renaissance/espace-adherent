<?php

namespace AppBundle\Admin;

use AppBundle\Entity\EntityContentInterface;
use League\CommonMark\CommonMarkConverter;
use Lullabot\AMP\AMP;

trait AmpSynchronisedAdminTrait
{
    /**
     * @var CommonMarkConverter
     */
    protected $markdown;

    /**
     * @param EntityContentInterface $object
     */
    public function preValidate($object): void
    {
        $html = $this->markdown->convertToHtml($object->getContent());

        $amp = new AMP();
        $amp->loadHtml($html);

        $object->setAmpContent($amp->convertToAmpHtml());
    }

    public function setMarkdown($markdown): void
    {
        $this->markdown = $markdown;
    }
}
