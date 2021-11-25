<?php

namespace App\Twig;

use App\Form\DeleteEntityType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRenderer;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class FormRuntime implements RuntimeExtensionInterface
{
    private $formFactory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->formFactory = $factory;
    }

    public function createDeleteForm(Environment $env, string $action, string $tokenId): string
    {
        return $env->getRuntime(FormRenderer::class)->renderBlock($this->formFactory->create(DeleteEntityType::class, null, [
            'action' => $action,
            'csrf_token_id' => $tokenId,
        ])->createView(), 'form');
    }
}
