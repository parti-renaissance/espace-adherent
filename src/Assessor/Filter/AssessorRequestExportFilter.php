<?php

namespace AppBundle\Assessor\Filter;

class AssessorRequestExportFilter
{
    /**
     * @var array
     */
    private $tags;

    /**
     * @var array
     */
    private $postalCodes;

    public function __construct(array $tags = [], array $postalCodes = [])
    {
        $this->tags = $tags;
        $this->postalCodes = $postalCodes;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getPostalCodes(): array
    {
        return $this->postalCodes;
    }
}
