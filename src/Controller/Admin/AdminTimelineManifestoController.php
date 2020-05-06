<?php

namespace App\Controller\Admin;

use A2lix\I18nDoctrineBundle\Annotation\I18nDoctrine;
use Sonata\AdminBundle\Controller\CRUDController as Controller;

class AdminTimelineManifestoController extends Controller
{
    /**
     * @I18nDoctrine
     */
    public function listAction()
    {
        return parent::listAction();
    }
}
