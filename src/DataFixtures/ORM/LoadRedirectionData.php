<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Redirection;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;

class LoadRedirectionData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createRedirection(
            '/dynamic-redirection-301',
            '/dynamic-redirection-301-target',
            Response::HTTP_MOVED_PERMANENTLY
        ));

        $manager->persist($this->createRedirection(
            '/dynamic-redirection-302',
            '/dynamic-redirection-302-target',
            Response::HTTP_FOUND
        ));

        $manager->flush();
    }

    private function createRedirection(string $from, string $to, int $type): Redirection
    {
        $redirection = new Redirection();
        $redirection->setFrom($from);
        $redirection->setTo($to);
        $redirection->setType($type);

        return $redirection;
    }
}
