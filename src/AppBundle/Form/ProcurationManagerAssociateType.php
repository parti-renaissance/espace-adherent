<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;

class ProcurationManagerAssociateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'app_procuration_manager_associate';
    }
}
