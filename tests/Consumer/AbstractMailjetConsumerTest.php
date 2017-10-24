<?php
namespace Tests\AppBundle\Consumer;

use AppBundle\Repository\MailjetEmailRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbstractMailjetConsumerTest extends TestCase
{
    const CLASS_NAME = 'AppBundle\Consumer\AbstractMailjetConsumer';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->registry = $this
            ->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->method('getManager')->willReturn($this->objectManager);
    }

    public function testDoExecuteWithoutUuidInMessage()
    {
        $uuid = 'aze-aze';

        $mailjetEmailRepository = $this->createMock(MailjetEmailRepository::class);
        $mailjetEmailRepository->expects($this->once())->method('findOneByUuid')->willReturn(null);

        $collections = $this->createMock(ArrayCollection::class)->method('count')->willReturn(0);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($collections);

        $abstractConsumer = $this
            ->getMockBuilder(self::CLASS_NAME)
            ->setConstructorArgs([$this->validator, $this->registry])
            ->setMethods(['getLogger'])
            ->getMockForAbstractClass();

        $message = $this->createMock(AMQPMessage::class);
        $message->body = json_encode(['uuid' => $uuid]);
        $logger = $this->createMock(LoggerInterface::class);

        $abstractConsumer->method('getLogger')->willReturn($logger);
        $this->assertEquals(ConsumerInterface::MSG_ACK, $abstractConsumer->execute($message));
    }
}
