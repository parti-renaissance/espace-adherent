<?php

namespace App\Twig;

use App\Entity\HomeBlock;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HomeBlockExtension extends AbstractExtension
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_home_block', [$this, 'getHomeBlock']),
        ];
    }

    public function getHomeBlock(string $positionName, bool $forRenaissance = false): HomeBlock
    {
        return $this->manager->getRepository(HomeBlock::class)->findOneBy([
            'positionName' => $positionName,
            'forRenaissance' => $forRenaissance,
        ]) ?? new HomeBlock();
    }
}
