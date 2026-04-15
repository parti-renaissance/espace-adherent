<?php

declare(strict_types=1);

namespace App\Admin\Jecoute;

use App\Controller\Admin\ZoneAutocompleteController;
use App\Form\Admin\AdminZoneAutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class JecouteManagedAreaAdmin extends AbstractAdmin
{
    public const SERVICE_ID = 'app.admin.jecoute.jecoute_managed_area_admin';

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('zone', AdminZoneAutocompleteType::class, [
            'preset' => ZoneAutocompleteController::PRESET_JECOUTE_MANAGED_AREA,
            'btn_add' => false,
        ]);
    }
}
