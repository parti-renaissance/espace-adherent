<?php

declare(strict_types=1);

namespace App\Form;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
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
    public const RATIO_16_9 = '16:9';

    public function buildForm(FormBuilderInterface $builder, array $options): void
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

            if (!empty($data['croppedImage'])) {
                if (str_contains($data['croppedImage'], 'base64,')) {
                    $imageData = explode('base64,', $data['croppedImage'], 2);
                    $content = $imageData[1];
                    $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
                    file_put_contents($tmpFile, base64_decode($content));

                    $data['image'] = new UploadedFile(
                        $tmpFile,
                        Uuid::uuid4()->toString().'.png',
                        str_replace([';', 'data:'], '', $imageData[0]),
                        null,
                        true
                    );

                    unset($data['croppedImage']);
                } elseif (-1 == $data['croppedImage']) {
                    unset($data['croppedImage'], $data['image']);
                    $model = $event->getForm()->getParent()->getData();
                    if (method_exists($model, $methodName = \sprintf('setRemove%s', ucfirst($event->getForm()->getName())))
                        || method_exists($model, $methodName = 'setRemoveImage')
                    ) {
                        $model->$methodName(true);
                    }
                }
            }

            $event->setData(!empty($data) ? $data : null);
        });

        $builder->addModelTransformer(new CallbackTransformer(
            function () { return null; },
            function ($value) {
                if (!empty($value['image']) && $value['image'] instanceof UploadedFile) {
                    return $value['image'];
                }

                return null;
            }
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['image_path'] = $options['image_path'];
        $view->vars['ratio'] = $options['ratio'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'image_path' => null,
                'ratio' => null,
                'label' => 'Ajouter une photo',
            ])
            ->setAllowedTypes('image_path', ['string', 'null'])
            ->setAllowedTypes('ratio', ['string', 'null'])
        ;
    }
}
