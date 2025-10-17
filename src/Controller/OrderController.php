<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/orders', name:'api_orders_')]
class OrderController extends AbstractController
{
    private int $kitchenCapacity = 5;

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, OrderRepository $orderRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate request
        if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
            return $this->json(['error' => 'Items are required'], 400);
        }

        if (!isset($data['pickup_time'])) {
            return $this->json(['error' => 'Pickup time is required'], 400);
        }

        $vip = $data['VIP'] ?? false;

        // Check kitchen capacity
        $activeOrders = $orderRepository->countActiveOrders();
        if (!$vip && $activeOrders >= $this->kitchenCapacity) {
            return $this->json(['error' => 'Kitchen is full'], 429);
        }

        // Create order entity
        $order = new Order();
        $order->setItems($data['items']);
        $order->setPickupTime(new \DateTime($data['pickup_time']));
        $order->setIsVip($vip);
        $order->setStatus(Order::STATUS_PENDING);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($order);
        $em->flush();

        return $this->json([
            'id' => $order->getId(),
            'items' => $order->getItems(),
            'pickup_time' => $order->getPickupTime()->format(DATE_ATOM),
            'VIP' => $order->isVip(),
            'status' => $order->getStatus()
        ], 201);
    }

    #[Route('/{id}/start', name:'start', methods: ['PATCH'])]
    public function startOrder(
        int $id,
        OrderRepository $orderRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $order = $orderRepository->find($id);

        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        if ($order->getStatus() !== Order::STATUS_PENDING) {
            return $this->json(['error' => 'Only pending orders can be started'], 400);
        }

        // Check kitchen capacity
        $activeOrders = $orderRepository->countActiveOrders();
        if ($activeOrders >= $this->kitchenCapacity && !$order->isVip()) {
            return $this->json(['error' => 'Kitchen is full'], 429);
        }

        $order->setStatus(Order::STATUS_ACTIVE);
        $em->persist($order);
        $em->flush();

        return $this->json([
            'id' => $order->getId(),
            'status' => $order->getStatus()
        ], 200);
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    public function completeOrder(
        int $id,
        OrderRepository $orderRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $order = $orderRepository->find($id);

        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $order->setStatus(Order::STATUS_COMPLETED);
        $em->persist($order);
        $em->flush();

        return $this->json([
            'id' => $order->getId(),
            'status' => $order->getStatus()
        ], 200);
    }

    #[Route('/active', name: 'list_active', methods: ['GET'])]
    public function listActive(OrderRepository $orderRepository): JsonResponse
    {
        $activeOrders = $orderRepository->findActiveOrders();

        $data = array_map(fn($order) => [
            'id' => $order->getId(),
            'items' => $order->getItems(),
            'pickup_time' => $order->getPickupTime()->format(DATE_ATOM),
            'VIP' => $order->isVip(),
            'status' => $order->getStatus(),
            'created_at' => $order->getCreatedAt()->format(DATE_ATOM)
        ], $activeOrders);

        return $this->json($data);
    }

}