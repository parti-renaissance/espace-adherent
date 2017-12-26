<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Twig;

use AppBundle\Form\DeleteEntityType;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\Form\FormFactoryInterface;
use Twig\Environment;

class FormRuntime
{
    private $formFactory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->formFactory = $factory;
    }

    public function createDeleteForm(Environment $env, string $action, string $tokenId): string
    {
        return $env->getRuntime(TwigRenderer::class)->renderBlock($this->formFactory->create(DeleteEntityType::class, null, [
            'action' => $action,
            'csrf_token_id' => $tokenId,
        ])->createView(), 'form');
    }
}
