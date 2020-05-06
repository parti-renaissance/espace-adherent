<?php

namespace App\Form;

use App\Form\DataTransformer\EmailToAdherentTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class AdherentEmailType extends AbstractType
{
    private $emailToAdherentTransformer;

    public function __construct(EmailToAdherentTransformer $adherentToEmailTransformer)
    {
        $this->emailToAdherentTransformer = $adherentToEmailTransformer;
    }

    public function getParent()
    {
        return EmailType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->emailToAdherentTransformer);
    }
}
