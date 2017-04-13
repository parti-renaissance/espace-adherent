<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageRepository")
 *
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Page
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;
    use EntityMediaTrait;

    const URLS = [
        'emmanuel-macron-ce-que-je-suis' => '/emmanuel-macron',
        'emmanuel-macron-revolution' => '/emmanuel-macron/revolution',
        'emmanuel-macron-propositions' => '/emmanuel-macron/le-programme',
        'le-mouvement-nos-valeurs' => '/le-mouvement',
        'le-mouvement-notre-organisation' => '/le-mouvement/notre-organisation',
        'le-mouvement-les-comites' => '/le-mouvement/les-comites',
        'le-mouvement-devenez-benevole' => '/le-mouvement/devenez-benevole',
        'mentions-legales' => '/mentions-legales',
        'le-mouvement-legislatives' => '/le-mouvement/legislatives',
        'desintox' => '/emmanuel-macron/desintox',
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @Algolia\Attribute
     */
    public function getStatic(): bool
    {
        return false;
    }

    /**
     * @Algolia\Attribute
     */
    public function getUrl(): string
    {
        $url = self::URLS[$this->slug] ?? null;
        if (!$url) {
            throw new \LogicException(sprintf(
                'Slug "%s" is not sync with AppBundle\Search\Algolia\PageSlugToUrl::URLS.',
                $this->slug
            ));
        }

        return $url;
    }
}
