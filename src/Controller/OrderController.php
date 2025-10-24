<?php

namespace App\Controller;

use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/orders', name:'api_orders_')]
class OrderController extends AbstractController
{
    public function __construct(private OrderService $orderService)
    {

    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $response = $this->orderService->createOrder($data);
        return $this->json($response);
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    public function completeOrder(int $id): JsonResponse 
    {
        $order = $this->orderService->completeOrder($id);
        return $this->json($order);
    }

    #[Route('/active', name: 'list_active', methods: ['GET'])]
    public function listActive(): JsonResponse
    {
        $orders = $this->orderService->listActiveOrders();
        return $this->json($orders);
    }

}