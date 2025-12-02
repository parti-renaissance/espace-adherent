<?php

declare(strict_types=1);

namespace App\DepartmentSite;

use App\Utils\StringCleaner;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Gedmo\Sluggable\Handler\SlugHandlerInterface;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Sluggable\SluggableListener;

class DepartmentSiteSlugHandler implements SlugHandlerInterface
{
    /**
     * @var callable
     */
    private $originalTransliterator;

    public function __construct(private readonly SluggableListener $sluggable)
    {
    }

    public function onChangeDecision(SluggableAdapter $ea, array &$config, $object, &$slug, &$needToChangeSlug)
    {
    }

    public function postSlugBuild(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
        $this->originalTransliterator = $this->sluggable->getTransliterator();
        $this->sluggable->setTransliterator([$this, 'transliterate']);
    }

    public function onSlugCompletion(SluggableAdapter $ea, array &$config, $object, &$slug)
    {
    }

    public function handlesUrlization()
    {
        return true;
    }

    public static function validate(array $options, ClassMetadata $meta)
    {
    }

    public function transliterate(string $text, string $separator, object $object): string
    {
        $result = \call_user_func_array($this->originalTransliterator, [$text, $separator, $object]);

        $this->sluggable->setTransliterator($this->originalTransliterator);

        if (method_exists($object, 'getZone') && $zone = $object->getZone()) {
            return StringCleaner::slugify($zone->getCode().'-'.$zone->getName());
        }

        return $result;
    }
}
