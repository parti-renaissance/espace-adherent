<?php

namespace AppBundle\Consumer\ChezVous;

use AppBundle\Algolia\AlgoliaIndexedEntityManager;
use AppBundle\Entity\ChezVous\MeasureType;
use AppBundle\Producer\ChezVous\AlgoliaProducer;
use AppBundle\Repository\ChezVous\CityRepository;
use AppBundle\Repository\ChezVous\MeasureTypeRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AlgoliaConsumer implements ConsumerInterface
{
    use LoggerAwareTrait;

    protected $logger;

    /**
     * @var CityRepository
     */
    private $cityRepository;

    /**
     * @var MeasureTypeRepository
     */
    private $measureTypeRepository;

    /**
     * @var AlgoliaIndexedEntityManager
     */
    private $algoliaIndexer;

    protected function configureDataConstraints(): array
    {
        return [
            'id' => [new Assert\NotBlank()],
        ];
    }

    public function execute(AMQPMessage $msg)
    {
        try {
            $data = \GuzzleHttp\json_decode($msg->getBody(), true);
        } catch (\Exception $e) {
            $this->getLogger()->error('Message is not valid JSON', [
                'message' => $msg->getBody(),
            ]);

            return ConsumerInterface::MSG_ACK;
        }

        try {
            if (!$measureType = $this->measureTypeRepository->find($data['id'])) {
                $this->getLogger()->error('MeasureType not found', $data);
                $this->writeln($data['uuid'], 'MeasureType not found, rejecting');

                return ConsumerInterface::MSG_ACK;
            }

            switch ($msg->get('routing_key')) {
                case AlgoliaProducer::KEY_MEASURE_TYPE_UPDATED:
                    $this->measureTypeUpdated($measureType);

                    break;
                case AlgoliaProducer::KEY_MEASURE_TYPE_DELETED:
                    $this->measureTypeDeleted($measureType);

                    break;
            }

            return ConsumerInterface::MSG_ACK;
        } catch (\Exception $error) {
            $this->getLogger()->error('Consumer failed', ['exception' => $error]);

            throw $error;
        }
    }

    public function setCityRepository(CityRepository $cityRepository): void
    {
        $this->cityRepository = $cityRepository;
    }

    public function setMeasureTypeRepository(MeasureTypeRepository $measureTypeRepository): void
    {
        $this->measureTypeRepository = $measureTypeRepository;
    }

    public function setAlgoliaIndexer(AlgoliaIndexedEntityManager $algoliaIndexer): void
    {
        $this->algoliaIndexer = $algoliaIndexer;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function writeln($name, $message): void
    {
        echo $name.' | '.$message.\PHP_EOL;
    }

    private function measureTypeUpdated(MeasureType $measureType): void
    {
        $cities = [];

        foreach ($this->cityRepository->findAllByMeasureType($measureType) as $city) {
            $cities[] = current($city);

            if (0 === (\count($cities) % 1000)) {
                $this->algoliaIndexer->batch($cities);
                $cities = [];
            }
        }

        if (!empty($cities)) {
            $this->algoliaIndexer->batch($cities);
        }
    }
}
