<?php

namespace AppBundle\Controller\Admin;

use A2lix\I18nDoctrineBundle\Annotation\I18nDoctrine;
use AppBundle\Entity\Timeline\Measure;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminTimelineMeasureController extends Controller
{
    /**
     * @I18nDoctrine
     */
    public function listAction()
    {
        return parent::listAction();
    }

    /**
     * @param Request $request
     * @param Measure $object
     */
    protected function preEdit(Request $request, $object)
    {
        // Algolia: needed to index Themes that could be removed from this Measure during edition
        $object->saveCurrentThemes();
    }
}
