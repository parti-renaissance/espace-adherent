<?php

namespace App\Form;

use App\Entity\ImageOwnerInterface;
use App\Form\DataTransformer\CroppedImageDataTransformer;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CroppedImageType extends AbstractType
{
    private $croppedImageDataTransformer;

    public function __construct(CroppedImageDataTransformer $croppedImageDataTransformer)
    {
        $this->croppedImageDataTransformer = $croppedImageDataTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('croppedImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['skip'])) {
                unset($data['croppedImage'], $data['biography'], $data['image']);
            } elseif (!empty($data['croppedImage'])) {
                if (false !== strpos($data['croppedImage'], 'base64,')) {
                    $imageData = explode('base64,', $data['croppedImage'], 2);
                    $content = $imageData[1];
                    $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
                    file_put_contents($tmpFile, base64_decode($content));

                    $data['image'] = new UploadedFile(
                        $tmpFile,
                        Uuid::uuid4()->toString().'.png',
                        str_replace([';', 'data:'], '', $imageData[0]),
                        null,
                        null,
                        true
                    );

                    unset($data['croppedImage']);
                } elseif (-1 == $data['croppedImage']) {
                    unset($data['croppedImage'], $data['image']);
                    /** @var ImageOwnerInterface $model */
                    $model = $event->getForm()->getParent()->getData();
                    $model->setRemoveImage(true);
                }
            }

            $event->setData(!empty($data) ? $data : null);
        });

        $builder->addModelTransformer($this->croppedImageDataTransformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['image_default_path'] = $options['image_default_path'];
        $view->vars['ratio'] = $options['ratio'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'image_default_path' => null,
            'ratio' => null,
        ])
            ->setAllowedTypes('image_default_path', ['string', 'null'])
            ->setAllowedTypes('ratio', ['string', 'null'])
        ;
    }
}
