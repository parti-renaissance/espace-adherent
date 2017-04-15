<?php

namespace AppBundle\Consumer;

use AppBundle\Exception\ReferentNotFoundException;
use AppBundle\Referent\ReferentDatabaseDumper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReferentManagedUsersDumperConsumer extends AbstractConsumer
{
    const NAME = 'referent-managed-users-dumper';

    protected function configureDataConstraints(): array
    {
        return [
            'referent_uuid' => [new NotBlank()],
            'referent_email' => [new NotBlank()],
            'type' => [
                new NotBlank(),
                new Choice([
                    'strict' => true,
                    'choices' => ReferentDatabaseDumper::EXPORT_TYPES,
                ]),
            ],
        ];
    }

    public function doExecute(array $data): bool
    {
        $logger = $this->getLogger();
        $dumper = $this->getDumper();

        try {
            $this->debug(sprintf('Dumping users of type %s for referent %s.', $data['type'], $data['referent_email']));
            $dumper->dump($data['referent_email'], $data['type']);
            $this->debug('Done.');
        } catch (ReferentNotFoundException $e) {
            $logger->error($e->getMessage());
            $this->writeln(self::NAME, 'Referent not found.');
        } catch (\Exception $e) {
            $logger->error(sprintf('Consumer %s failed.', self::NAME), ['exception' => $e]);

            return false;
        }

        return true;
    }

    private function getDumper(): ReferentDatabaseDumper
    {
        return $this->container->get('app.referent.database_dumper');
    }

    private function getLogger(): LoggerInterface
    {
        return $this->container->get('logger');
    }

    private function debug(string $message): void
    {
        $this->writeln(self::NAME, $message);
    }
}
