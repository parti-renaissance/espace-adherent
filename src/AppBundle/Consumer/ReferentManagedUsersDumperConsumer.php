<?php

namespace AppBundle\Consumer;

use AppBundle\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class ReferentManagedUsersDumperConsumer extends AbstractConsumer
{
    const NAME = 'referent-managed-users-dumper';

    protected function configureDataConstraints(): array
    {
        return [
            'referent_uuid' => [new Assert\NotBlank()],
            'referent_email' => [new Assert\NotBlank()],
            'type' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'strict' => true,
                    'choices' => [
                        'all',
                        'subscribers',
                        'adherents',
                        'non_followers',
                        'followers',
                        'hosts',
                        'serialized',
                    ],
                ]),
            ],
        ];
    }

    public function doExecute(array $data): bool
    {
        $logger = $this->container->get('logger');
        $repository = $this->container->get('doctrine')->getManager()->getRepository(Adherent::class);
        $managedUsersFactory = $this->container->get('app.referent.managed_users.factory');
        $managedUsersExporter = $this->container->get('app.referent.managed_users.exporter');
        $storage = $this->container->get('app.storage');

        $type = $data['type'];

        try {
            $this->writeln(self::NAME, 'Dumping users of type '.$type.' for referent '.$data['referent_email']);

            $referent = $repository->findByUuid($data['referent_uuid']);

            if (!$referent) {
                $logger->error('Referent not found', $data);
                $this->writeln(self::NAME, 'Referent not found');

                return true;
            }

            if ('serialized' === $type) {
                $users = $managedUsersFactory->createManagedUsersListIndexedByTypeAndId($referent);
            } elseif ('subscribers' === $type) {
                $users = $managedUsersFactory->createManagedSubscribersCollectionFor($referent);
            } elseif ('adherents' === $type) {
                $users = $managedUsersFactory->createManagedAdherentsCollectionFor($referent);
            } elseif ('non_followers' === $type) {
                $users = $managedUsersFactory->createManagedNonFollowersCollectionFor($referent);
            } elseif ('followers' === $type) {
                $users = $managedUsersFactory->createManagedFollowersCollectionFor($referent);
            } elseif ('hosts' === $type) {
                $users = $managedUsersFactory->createManagedHostsCollectionFor($referent);
            } else {
                $users = $managedUsersFactory->createManagedUsersCollectionFor($referent);
            }

            if ('serialized' === $type) {
                $exported = serialize($users);
            } else {
                $exported = $managedUsersExporter->exportAsJson($users);
            }

            $filename = 'dumped_referents_users/'.$data['referent_uuid'].'_'.$data['type'].'.data';

            if (!$storage->has('dumped_referents_users')) {
                $storage->createDir('dumped_referents_users');
            }

            return $storage->put($filename, $exported);
        } catch (\Exception $error) {
            $logger->error('Consumer referent-managed-users-dumper failed', ['exception' => $error]);

            return false;
        }
    }
}
