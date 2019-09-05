<?php

namespace AppBundle\Consumer\ChezVous;

use AppBundle\Algolia\AlgoliaIndexedEntityManager;
use AppBundle\Consumer\AbstractConsumer;
use AppBundle\Repository\ChezVous\CityRepository;
use AppBundle\Repository\ChezVous\MeasureTypeRepository;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AlgoliaConsumer extends AbstractConsumer
{
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

    public function doExecute(array $data): int
    {
        try {
            if (!$measureType = $this->measureTypeRepository->find($data['id'])) {
                $this->getLogger()->error('MeasureType not found', $data);
                $this->writeln($data['uuid'], 'MeasureType not found, rejecting');

                return ConsumerInterface::MSG_ACK;
            }

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
}
