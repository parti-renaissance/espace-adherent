<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Address\GeoCoder;
use AppBundle\Event\BaseEventCommand;
use Symfony\Component\Form\DataTransformerInterface;

class EventDateTimeZoneTransformer implements DataTransformerInterface
{
    /**
     * @param BaseEventCommand $baseEventCommand
     */
    public function transform($baseEventCommand): BaseEventCommand
    {
        $begintAt = $this->transformDateTime(
            $baseEventCommand->getBeginAt(),
            $baseEventCommand->getTimeZone()
        );
        $baseEventCommand->setBeginAt($begintAt);

        $finishAt = $this->transformDateTime(
            $baseEventCommand->getFinishAt(),
            $baseEventCommand->getTimeZone()
        );
        $baseEventCommand->setFinishAt($finishAt);

        return $baseEventCommand;
    }

    /**
     * @param BaseEventCommand $baseEventCommand
     */
    public function reverseTransform($baseEventCommand): BaseEventCommand
    {
        $begintAt = $this->reverseTransformDateTime(
            $baseEventCommand->getBeginAt(),
            $baseEventCommand->getTimeZone()
        );
        $baseEventCommand->setBeginAt($begintAt);

        $finishAt = $this->reverseTransformDateTime(
            $baseEventCommand->getFinishAt(),
            $baseEventCommand->getTimeZone()
        );
        $baseEventCommand->setFinishAt($finishAt);

        return $baseEventCommand;
    }

    private function transformDateTime(\DateTime $dateTime, string $timeZone): \DateTimeInterface
    {
        if (GeoCoder::DEFAULT_TIME_ZONE !== $timeZone) {
            return $dateTime->setTimezone(new \DateTimeZone($timeZone));
        }

        return $dateTime;
    }

    private function reverseTransformDateTime(\DateTimeInterface $dateTime, string $timeZone): \DateTimeInterface
    {
        if (GeoCoder::DEFAULT_TIME_ZONE !== $timeZone) {
            $dateTime = new \DateTime($dateTime->format('Y-m-d H:i'), new \DateTimeZone($timeZone));

            return $dateTime->setTimezone(new \DateTimeZone(GeoCoder::DEFAULT_TIME_ZONE));
        }

        return $dateTime;
    }
}
