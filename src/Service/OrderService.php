<?php
namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderService
{
    private const KITCHEN_LIMIT = 5;
    
    public function __construct(private EntityManagerInterface $em)
    {

    }

    public function createOrder(array $data): array
    {
        $this->validateOrderData($data);

        $activeOrders = $this->em->getRepository(Order::class)->count(['status' => 'active']);
        $isVip = $data['VIP'] ?? false;

        if ($activeOrders >= self::KITCHEN_LIMIT && !$isVip) {
            return array('error' => 'Kitchen is full', 'code' => 429);
        }

        $order = new Order();
        $order->setItems($data['items']);
        $order->setPickupTime(new \DateTime($data['pickup_time']));
        $order->setIsVip($isVip);
        $order->setStatus(Order::STATUS_ACTIVE);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($order);
        $this->em->flush();

        return [$order, 'code' => 201];
    }

    public function listActiveOrders(): array
    {
        return $this->em->getRepository(Order::class)->findBy(['status' => 'active']);
    }

    public function completeOrder(int $id): array
    {
        $order = $this->em->getRepository(Order::class)->find($id);

        if (!$order) {
            return array('error' => 'Order not found', 'code' => 404);
        }

        $order->setStatus('completed');
        $this->em->flush();

        return [$order, 'code' => 201];
    }

    private function validateOrderData(array $data): array
    {
        $errors = [];

        if (empty($data['items']) || !is_array($data['items'])) {
            return array('error' => 'Items are required', 'code' => 400);
        }

        if (empty($data['pickup_time']) || !strtotime($data['pickup_time'])) {
            return array('error' => 'Pickup time is required', 'code' => 400);
        }

        return $errors;
    }
}