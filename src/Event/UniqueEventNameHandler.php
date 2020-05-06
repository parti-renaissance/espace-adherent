<?php

namespace App\Event;

use App\Entity\Event;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerWithUniqueCallbackInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;

/**
 * This is Sluggable handler which used on BaseEvent::slug property to modify
 * Event name when the slug value is modified by SluggableListener to avoid duplicate value.
 */
class UniqueEventNameHandler implements SlugHandlerWithUniqueCallbackInterface
{
    private $initialSlug;

    public function __construct(SluggableListener $sluggable)
    {
    }

    public function beforeMakingUnique(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $this->initialSlug = $slug;
    }

    /**
     * @param Event $object
     */
    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        if ($this->initialSlug && $slug !== $this->initialSlug) {
            $object->setName(sprintf('%s (%d)', $object->getName(), substr(strrchr($slug, $config['separator']), 1)));
        }
    }

    public function handlesUrlization()
    {
    }

    public static function validate(array $options, ClassMetadata $meta)
    {
    }

    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
    }

    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }
}
