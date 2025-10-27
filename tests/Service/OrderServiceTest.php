<?php
namespace App\Tests\Service;

use App\Entity\Order;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private $em;
    private $repository;
    private $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EntityRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        // Mock getRepository() to return our fake repository
        $this->em->method('getRepository')->willReturn($this->repository);

        $this->service = new OrderService($this->em);
    }

    public function testCreateOrderFailsWhenKitchenFullForNonVip()
    {
        // Arrange
        $this->repository->method('count')->willReturn(5);

        $data = [
            'items' => ['Burger', 'Fries'],
            'pickup_time' => '2025-10-25 12:00:00',
            'VIP' => false
        ];

        // Act
        $result = $this->service->createOrder($data);

        // Assert
        $this->assertEquals('Kitchen is full', $result['error']);
        $this->assertEquals(429, $result['code']);
    }

    public function testCreateOrderSucceedsForVipEvenWhenKitchenFull()
    {
        $this->repository->method('count')->willReturn(5);

        $data = [
            'items' => ['Pizza'],
            'pickup_time' => '2025-10-25 14:00:00',
            'VIP' => true
        ];

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->createOrder($data);

        $this->assertInstanceOf(Order::class, $result[0]);
        $this->assertEquals(201, $result['code']);
    }

    public function testCreateOrderFailsForMissingItems()
    {
        $data = [
            'pickup_time' => '2025-10-25 12:00:00'
        ];

        $result = $this->service->createOrder($data);

        $this->assertEquals('Items are required', $result['error']);
        $this->assertEquals(400, $result['code']);
    }

    public function testCompleteOrderFailsWhenNotFound()
    {
        $this->repository->method('find')->willReturn(null);

        $result = $this->service->completeOrder(99);

        $this->assertEquals('Order not found', $result['error']);
        $this->assertEquals(404, $result['code']);
    }

    public function testCompleteOrderSuccess()
    {
        // Arrange
        $order = new Order();
        $order->setItems(['Pasta']);
        $order->setPickupTime(new \DateTime('2025-10-25 18:00:00'));
        $order->setIsVip(false);
        $order->setStatus('active');

        $this->repository->method('find')->willReturn($order);

        $this->em->expects($this->once())->method('flush');

        // Act
        $result = $this->service->completeOrder(1);

        // Assert
        $this->assertEquals('completed', $order->getStatus());
        $this->assertEquals(201, $result['code']);
        $this->assertEquals('completed', $result[0]->getStatus());
    }
}
